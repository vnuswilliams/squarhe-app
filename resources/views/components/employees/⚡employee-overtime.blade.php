<?php

use App\Enums\HsuppEnum;
use App\Jobs\ImportEmployeeOvertimesJob;
use App\Livewire\Forms\EmployeeOvertimeForm;
use App\Models\Overtime;
use App\Services\CalculateHsupp;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use WithFileUploads;

    public $employee;
    public $importFile;
    public array $previewRows = [];
    public array $importErrors = [];
    public bool $readyToImport = false;
    public EmployeeOvertimeForm $form;
    public function mount($employee)
    {
        $this->employee = $employee;
        $this->form->hours_rate = app(CalculateHsupp::class)->hourRate($this->employee);
    }
    #[Computed]
    public function overtimes()
    {
        return $this->employee->overtimes ?? [];
    }
    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->multiplier = HsuppEnum::from($this->form->day_type)->dayType();
        $this->form->create();
        $this->showOvertimeForm = false;
        Flux::toast(variant: 'success', text: __('Heure(s) supp(s).  ajoutée(s) avec  succès.'));
        $this->form->resetExcept('hours_rate');
    }
    public function edit($overtimeId)
    {
        $overtimeToUpdate  = Overtime::whereId($overtimeId)->whereEmployeeId($this->employee->id)
            ->firstOrFail();

        $this->form->setOvertime($overtimeToUpdate);
        Flux::modal('edit-overtime-modal')->show();
    }

    public function update()
    {
        $this->form->multiplier = HsuppEnum::from($this->form->day_type)->dayType();
        $this->form->update();
        Flux::modal('edit-overtime-modal')->close();
        Flux::toast(variant: 'success', text: 'Heure(s) supp(s). a été mise(s) à jour avec succès.');
        $this->form->resetExcept('hours_rate');
    }
    public $overtimeToDelete = null;
    public function confirmBeforeDelete($idOvertimeWeWantToDelete)
    {
        $this->overtimeToDelete = Overtime::whereId($idOvertimeWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-overtime-modal')->show();
    }
    public function delete()
    {
        if ($this->overtimeToDelete):
            Gate::authorize('delete', [Overtime::class, $this->overtimeToDelete]);
            $this->overtimeToDelete->delete();
            Flux::toast(variant: 'success', text: 'Heure(s) supp(s). supprimé(e)s avec succès.');
            Flux::modal('delete-overtime-modal')->close();
            $this->overtimeToDelete = null;
        endif;
    }

    public $showOvertimeForm = false;
    public function toggleFormOvertime(): void
    {
        $this->showOvertimeForm = !$this->showOvertimeForm;
    }

    public function previewImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);


        $rows = (new FastExcel())->import($this->importFile->getRealPath());
        $this->previewRows = [];
        $this->importErrors = [];

        foreach ($rows as $index => $row) {
            $data = [
                'day_type' => $row['day_type'] ?? null,
                'hours' => $row['hours'] ?? null,
                'hours_rate' => $row['hours_rate'] ?? null,
                'week' => $row['week'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $validator = Validator::make($data, [
                'day_type' => ['required', \Illuminate\Validation\Rule::in(HsuppEnum::values())],
                'hours' => ['required', 'numeric', 'min:1'],
                'hours_rate' => ['required', 'numeric', 'min:1'],
                'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
                'notes' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) $this->importErrors[] = ['line' => $index + 2, 'errors' => $validator->errors()->all()];
            $this->previewRows[] = $data;
        }
        $this->readyToImport = count($this->importErrors) === 0 && count($this->previewRows) > 0;
    }

    public function confirmImport(): void
    {
        if (! $this->readyToImport) { Flux::toast(variant: 'danger', text: __('Corrigez les erreurs avant import.')); return; }
        $path = $this->importFile->store('imports');
        ImportEmployeeOvertimesJob::dispatch($path, $this->employee->id, auth()->id());
        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import lancé. Le traitement est en cours.'));
    }

};
?>

<div  x-data="{ activeForm : null }">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-lg">{{ __('Heures supplémentaires') }}</h3>

            </div>
            <p class="text-gray-400 text-sm">{{ __('Gérez les heures supplémenttaires de votre collaborateur') }}</p>
        </div>
        <div>
            <flux:button @click="activeForm = 'a' " variant="primary">
                {{ __('Ajouter des heures supps') }}
            </flux:button>
            <flux:button @click="activeForm = 'b'" variant="ghost">
                {{ __('Prévisualiser') }}
            </flux:button>

        </div>

    </div>

    <x-container x-show="activeForm === 'a' "  x-transition>
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-4 p-2 ">


                    <flux:input wire:model="form.week" label="{{ __('Numéro de la semaine.') }}" placeholder="numéro de la semaine entre 1 & 5" type="number" />

                    <flux:select wire:model="form.day_type" label="{{ __('Quel type d\'heures supps') }}">
                        <flux:select.option value="">{{ __('Choisir une option') }}</flux:select.option>
                        @foreach (HsuppEnum::options() as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label']  }}
                        </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="form.hours" label="{{ __('Nbres d\'heures supps') }}" />

                    <flux:input wire:model="form.hours_rate" label="{{ __('Taux horaire') }}" />
                    <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>


                </div>

            <div class="flex justify-end items-center gap-2">
                <flux:button @click="activeForm = null " >
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">{{ __('overtime.button.save') }}</flux:button>
            </div>
        </form>
            @if(!empty($previewRows))
                <div class="mt-4">
                    <flux:text>{{ __("Lignes prévisualisées") }}: {{ count($previewRows) }}</flux:text>
                    @if(!empty($importErrors))
                        <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                            <flux:callout.heading>{{ __("Erreurs détectées") }}</flux:callout.heading>
                            <flux:callout.text>
                                @foreach($importErrors as $error)
                                    <div>{{ __("Ligne") }} {{ $error["line"] }}: {{ implode(", ", $error["errors"]) }}</div>
                                @endforeach
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                    <div class="flex justify-end mt-3">
                        <flux:button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport">{{ __("Valider et importer") }}</flux:button>
                    </div>
                </div>
            @endif


        <flux:callout icon="information-circle" class="mt-5">
            <flux:callout.heading> Information</flux:callout.heading>
            <flux:callout.text>
                La base de calcul est égale au : (salaire catégoriel échelonné + diverses primes assimilées au salaire
                (prime de technicité, de rendement, de fonction))*nbres d'heures * pourcentage des heures supplémentaires.
                <flux:callout.link href="#">{{ __() }}</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    </x-container>

    <x-container x-show="activeForm === 'b'" x-transition>
        <form wire:submit="previewImport" class="space-y-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-2">
                <flux:button @click="activeForm = null">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Importer') }}</flux:button>
            </div>
        </form>
    </x-container>

    @if(!$this->overtimes->isEmpty())
    <x-delta-card :cards="[
        [
            'label' => 'Total heure supp. ce mois',
            'current' => $this->overtimes->sum('hours'),
            'prev' => now()->format('M Y'),
            'delta' => '',
            'up' => true,
            'color' => 'blue',
        ],
        [
            'label' => 'Allocation hsupps. estimées',
            'current' =>  number_format($this->overtimes->sum('alloc'), 0, ',', ' ')  . ' F cfa',
            'prev' => 'Ajout au brut',
            'delta' => '',
            'up' => true,
            'color' => 'emerald',
        ],
        [
            'label' => 'Taux horaire',
            'current' =>  number_format($this->form->hours_rate, 0, ',', ' ')  . ' F cfa',
            'prev' => 'Ajout au brut',
            'delta' => '',
            'up' => true,
            'color' => 'emerald',
        ]
    ]" />


    @endif

    <x-container>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Semaine') }}

                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}

                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Heures') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Taux horaire') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Alloc estimés') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Ajouté par') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->overtimes as $overtime)
                <tr wire:key="{{ $overtime->id }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $overtime?->week }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $overtime->day_type->label() }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $overtime->notes }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $overtime->hours }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $overtime->hours_rate }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $overtime->alloc }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $overtime->added_by }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="edit({{ $overtime->id }})" size="sm" variant="ghost" icon="pencil" />
                            <flux:button wire:click="confirmBeforeDelete({{ $overtime->id }})" size="sm" variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucune heures supps. trouvé(s) pour '). $this->employee->name.'.' }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </x-container>



    <flux:modal name="edit-overtime-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Mettre à jour l'heure supp.</flux:heading>
            </div>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-4 p-2 ">


                    <flux:input wire:model="form.week" label="{{ __('Numéro de la semaine.') }}" placeholder="numéro de la semaine entre 1 & 5" type="number" />

                    <flux:select wire:model="form.day_type" label="{{ __('Quel type d\'heures supps') }}">
                        <flux:select.option value="">{{ __('Choisir une option') }}</flux:select.option>
                        @foreach (HsuppEnum::options() as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label']  }}
                        </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="form.hours" label="{{ __('Nbres d\'heures supps') }}" />

                    <flux:input wire:model="form.hours_rate" label="{{ __('Taux horaire') }}" />
                    <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>


                </div>

                <div class="flex justify-end gap-2  pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <flux:modal name="delete-overtime-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce congé ou absence</flux:heading>
            </div>
            @if($overtimeToDelete)
            <p>
                Voulez vous vraiment supprimer {{$overtimeToDelete->day_type }} ajouté par {{ $overtimeToDelete->added_by }} ?
            </p>
            <p>Cette action est irréversiblee.</p>
            @endif

            <div class="flex justify-end gap-2  pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>

                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
