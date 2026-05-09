<?php

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Jobs\ImportEmployeeRemunerationsJob;
use App\Livewire\Forms\EmployeeRemunerationForm;
use App\Models\Remuneration;
use Flux\Flux;
use Rap2hpoutre\FastExcel\Facades\FastExcel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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

    public EmployeeRemunerationForm $form;


    public function mount()
    {
        $this->avgSalary = $this->employee->data['average_salary'] ?? 0;
        $this->smic = $this->employee->data['smic'] ?? 0;
    }

    #[Computed]
    public function remunerations()
    {
        return $this->employee->remunerations ?? [];
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->type = RemunerationEnum::from($this->form->name)->type();

        $this->form->create();
        Flux::toast(variant: 'success', text: __("L'élément de rémun. a été ajouté avec  succès."));
        $this->form->reset();
    }

    public function edit($remunId)
    {
        $remunToUpdate = Remuneration::whereId($remunId)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        $this->form->setRemun($remunToUpdate);
        Flux::modal('edit-remuneration-modal')->show();
    }

    public function update()
    {
        $this->form->update();
        $this->form->reset();
        Flux::modal('edit-remuneration-modal')->close();
        Flux::toast(variant: 'success', text: "L'élément de remun. a été mis à jour avec succès.");
    }

    public $remunerationToDelete = null;

    public function confirmBeforeDelete($idRemunWeWantToDelete)
    {
        $this->remunerationToDelete = Remuneration::whereId($idRemunWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-remuneration-modal')->show();
    }

    public function delete()
    {
        if ($this->remunerationToDelete) {
            Gate::authorize('delete', [Remuneration::class, $this->remunerationToDelete]);
            $this->remunerationToDelete->delete();
            Flux::toast(variant: 'success', text: 'Cet élément de remun. a été supprimé avec succès.');
            Flux::modal('delete-remuneration-modal')->close();
            $this->remunerationToDelete = null;
        }
    }

    public $avgSalary;

    public $smic;

    public $snapshotRef = '';
    public $showRemunerationArchives = false;


    #[Computed]
    public function remunerationsSnapshot()
    {
        $query = $this->employee->remunerationsSnapshot()->latest();

        if (filled($this->snapshotRef)) {
            $query->where('ref', 'like', '%' . trim($this->snapshotRef) . '%');
        }

        return $query->get();
    }


    public function toggleRemunerationArchives(): void
    {
        $this->showRemunerationArchives = !$this->showRemunerationArchives;
    }

    public function exportRemunerationArchives()
    {
        $rows = $this->remunerationsSnapshot->map(fn ($snapshot) => [
            __('Ref') => $snapshot->ref,
            __('Nom') => $snapshot->name?->label(),
            __('Type') => $snapshot->type?->label(),
            __('Montant') => $snapshot->amount,
            __('Périodicité') => $snapshot->periodicity?->label(),
            __('Impact') => $snapshot->impact?->label(),
        ]);

        return new FastExcel($rows)->download('archives_remunerations_' . $this->employee->id . '_' . now()->format('m_Y') . '.xlsx');
    }

    public function addAvgSalary()
    {
        $data = $this->employee->data;

        $this->validate([
            'avgSalary' => 'nullable|numeric|min:1',
            'smic' => 'nullable|numeric|min:1',
        ]);
        $data['smic'] = $this->avgSalary;
        $data['average_salary'] = $this->smic;

        $this->employee->update([
            'data' => $data,
        ]);

        Flux::toast(variant: 'success', text: 'Vous avez mis a jour le smic et le salaire moyen.');
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
                'name' => $row['name'] ?? null,
                'amount' => $row['amount'] ?? null,
                'periodicity' => $row['periodicity'] ?? null,
                'impact' => $row['impact'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $validator = Validator::make($data, [
                'name' => ['required', \Illuminate\Validation\Rule::in(RemunerationEnum::values())],
                'amount' => ['required', 'numeric', 'min:100'],
                'periodicity' => ['required', \Illuminate\Validation\Rule::in(PeriodicityEnum::values())],
                'impact' => ['required', \Illuminate\Validation\Rule::in(ImpactEnum::values())],
                'notes' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                $this->importErrors[] = ['line' => $index + 2, 'errors' => $validator->errors()->all()];
            }

            $this->previewRows[] = $data;
        }

        $this->readyToImport = count($this->importErrors) === 0 && count($this->previewRows) > 0;
    }

    public function confirmImport(): void
    {
        if (! $this->readyToImport) {
            Flux::toast(variant: 'danger', text: __('Corrigez les erreurs avant import.'));
            return;
        }

        $path = $this->importFile->store('imports');
        ImportEmployeeRemunerationsJob::dispatch($path, $this->employee->id, auth()->id());
        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import lancé. Le traitement est en cours.'));
    }

    public function downloadTemplate()
    {
        $path = 'templates/remunerations_import_template.xlsx';

        if (!Storage::exists($path)) {
            $rows = collect([[
                'name' => RemunerationEnum::SUR_SALAIRE->value,
                'amount' => 10000,
                'periodicity' => PeriodicityEnum::MONTHLY->value,
                'impact' => ImpactEnum::NEUTRE->value,
                'notes' => 'Exemple',
            ]]);

            (new FastExcel($rows))->export(Storage::path($path));
        }

        return Storage::download($path);
    }

  
};
?>

<div x-data="{ activeForm : null }">
    <div class="flex justify-between items-center mb-4">
        <div>
            <flux:heading level="1" class="font-bold"> Éléments de rémunération </flux:heading>
            <flux:text class="text-gray-300">Primes, retenues, et autres variables de paie appliqués a cet employé.</flux:text>
        </div>

        <div class="flex items-center gap-2">

            <flux:button @click="activeForm = 'a' " variant="primary">
                Ajouter un élément
            </flux:button>
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item @click="activeForm = 'b' ">
                        {{ __('Add average salary') }}
                    </flux:menu.item>
                    <flux:menu.item @click="activeForm = 'c'">
                        {{ __('Importer des éléments') }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="downloadTemplate">
                        {{ __('Télécharger le template') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

        </div>

    </div>

    <x-container x-show="activeForm === 'a' "  x-transition>
        <flux:heading level="1" size="lg" class="mb-5"> Ajouter des éléments de rémunération de votre employé </flux:heading>
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">

                    <div>
                        <flux:select label="Nom de l'élément" wire:model="form.name">
                            <flux:select.option value="">Choisir un élément</flux:select.option>
                            @foreach(RemunerationEnum::forSelect() as $option)
                            <flux:select.option value="{{ $option->value }}">
                                {{ $option->name }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:input label="Montant" placeholder="Montant de l'élèment" wire:model="form.amount" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <flux:select label="Périodicité" wire:model="form.periodicity">
                            <flux:select.option value="">Choisir</flux:select.option>
                            @foreach(PeriodicityEnum::options() as $option)
                            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:select label="Impact" wire:model="form.impact">
                            <flux:select.option value="">Choisir</flux:select.option>
                            @foreach(ImpactEnum::options() as $option)
                            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}
                            </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div>
                        <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end items-center mt-5 gap-4">
                <flux:button type="button" @click="activeForm = null ">Annuler</flux:button>
                <flux:button type="submit" variant="primary">
                    Enregistrer
                </flux:button>
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

    </x-container>

    <x-container x-show="activeForm === 'c' " x-transition>
        <flux:heading level="1" size="lg" class="mb-5">{{ __('Importer les éléments de rémunération') }}</flux:heading>
        <form wire:submit="previewImport" class="space-y-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-4">
                <flux:button type="button" @click="activeForm = null">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Prévisualiser') }}</flux:button>
            </div>
        </form>
        @if(!empty($previewRows))
            <div class="mt-4">
                <flux:text>{{ __('Lignes prévisualisées') }}: {{ count($previewRows) }}</flux:text>
                @if(!empty($importErrors))
                    <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                        <flux:callout.heading>{{ __('Erreurs détectées') }}</flux:callout.heading>
                        <flux:callout.text>
                            @foreach($importErrors as $error)
                                <div>{{ __('Ligne') }} {{ $error['line'] }}: {{ implode(', ', $error['errors']) }}</div>
                            @endforeach
                        </flux:callout.text>
                    </flux:callout>
                @endif
                <div class="flex justify-end mt-3">
                    <flux:button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport">{{ __('Valider et importer') }}</flux:button>
                </div>
            </div>
        @endif
    </x-container>


    <x-container  x-show="activeForm === 'b' " x-transition>
        <flux:heading level="1" size="lg" class="mb-5"> Ajouter le salaire moyen et le smic de {{ $employee->name }} </flux:heading>
        <form wire:submit="addAvgSalary" class="">
            <flux:input wire:model="avgSalary" label="Salaire moyen" />
            <flux:input wire:model="smic" label="SMIC du secteur " />


            <flux:callout class="m-4" icon="information-circle">
                <flux:callout.heading>Information</flux:callout.heading>

                <flux:callout.text>
                    <ul>
                        <li>Salaire moyen : il sert à calculer les allocations congés annuel payé de votre employé. </li>
                        <li>SMIC du secteur : il sert à calculer la prime d'ancienneté.</li>
                    </ul>
                    <flux:text class="text-bold">Si non fourni le salaire de base sera utilisé commme base de calcul.</flux:text>
                </flux:callout.text>
            </flux:callout>


            <div class="flex justify-end items-center gap-4">
                <flux:button @click="activeForm = null "> {{ __('Cancel') }} </flux:button>
                <flux:button type="submit" variant="primary">Ajouter</flux:button>

            </div>
        </form>
    </x-container>

   @if($this->remunerations->isNotEmpty())

    <x-delta-card :cards="[
            [
                'label' => 'Total éléments de rémunération',
                'current' => $this->remunerations->sum('amount').' F cfa',
                'delta' => '',
                'color' => 'blue'
            ],
            [
                'label' => 'Eléments côtisable',
                'current' =>  $this->remunerations->where('impact', ImpactEnum::TAXCOT)->sum('amount') +
                $this->remunerations->where('impact', ImpactEnum::COTISABLE)->sum('amount').' F cfa',
                'delta' => '',
                'color' => 'emerald'
            ],
            [
                'label' => 'Eléments taxable',
                'current' =>  $this->remunerations->where('impact', ImpactEnum::TAXCOT)->sum('amount') +
                $this->remunerations->where('impact', ImpactEnum::TAXABLE)->sum('amount').' F cfa',
                'delta' => '',
                'color' => 'rose'
            ],
            [
                'label' => 'Eléments neutres',
                'current' =>  $this->remunerations->where('impact', ImpactEnum::NEUTRE)->sum('amount') .' F cfa',
                'delta' => '',
                'color' => 'rose'
            ]
        ]" />



    @endif
        <x-container>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Nom') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}

                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Montant') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Périodicité') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Impact') }}
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
                @forelse($this->remunerations as $remun)
                <tr wire:key="{{ $remun->id }}">

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $remun->name->label() }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $remun->notes }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $remun->type->label() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $remun->amount }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $remun->periodicity->label() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $remun->impact->label()}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $remun->added_by }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="edit({{ $remun->id }})" size="sm" variant="ghost" icon="pencil" />
                            <flux:button wire:click="confirmBeforeDelete({{ $remun->id }})" size="sm" variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucun élément(s) de rémun. trouvé(s) pour '). $this->employee->name.'.' }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </x-container>


    <div class="mt-4 mb-2 flex items-center gap-2">
        <flux:button @click="activeForm = activeForm === 'archives-remunerations' ? null : 'archives-remunerations'" variant="filled">
            {{ __('Afficher les archives') }}
        </flux:button>
    </div>

    <x-container x-show="activeForm === 'archives-remunerations'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">Historique des rémunérations (snapshots)</flux:heading>
                <flux:text>Filtrez par ref (format m-Y).</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :label="__('Filtrer par ref')" :placeholder="__('ex: 05-2026')" />
                <flux:button wire:click="exportRemunerationArchives" icon="arrow-up-tray">{{ __('Exporter') }}</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Ref</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Nom</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Type</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Montant</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Périodicité</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Impact</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->remunerationsSnapshot as $snapshot)
                <tr wire:key="remu-snapshot-{{ $snapshot->id }}">
                    <td class="px-6 py-4 text-sm">{{ $snapshot->ref }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->name?->label() }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->type?->label() }}</td>
                    <td class="px-6 py-4 text-sm">{{ number_format($snapshot->amount, 0, ',', ' ') }} F cfa</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->periodicity?->label() }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->impact?->label() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8">Aucune rémunération snapshot trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-container>

    <flux:modal name="edit-remuneration-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Mettre à jour un congé ou une absence</flux:heading>
            </div>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">

                        <div>
                            <flux:select label="Nom de l'élément" wire:model="form.name">
                                <flux:select.option value="">Choisir un élément</flux:select.option>
                                @foreach(RemunerationEnum::forSelect() as $option)
                                <flux:select.option value="{{ $option->value }}">
                                    {{ $option->name }}
                                </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:input label="Montant" placeholder="Montant de l'élèment" wire:model="form.amount" />
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <flux:select label="Périodicité" wire:model="form.periodicity">
                                <flux:select.option value="">Choisir</flux:select.option>
                                @foreach(PeriodicityEnum::options() as $option)
                                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}
                                </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:select label="Impact" wire:model="form.impact">
                                <flux:select.option value="">Choisir</flux:select.option>
                                @foreach(ImpactEnum::options() as $option)
                                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}
                                </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2  pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <flux:modal name="delete-remuneration-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce congé ou absence</flux:heading>
            </div>
            @if($remunerationToDelete)
            <p>
                Voulez vous vraiment supprimer {{$remunerationToDelete->name->label()}} ajouté par {{ $remunerationToDelete->added_by }} ?
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
