<?php

use App\Enums\HsuppEnum;
use App\Livewire\Forms\EmployeeOvertimeForm;
use App\Models\Overtime;
use App\Services\CalculateHsupp;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Rap2hpoutre\FastExcel\Facades\FastExcel;
use Livewire\Component;

new class extends Component
{
    public $employee;
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
    public $snapshotRef = '';
    public $showOvertimeArchives = false;

    #[Computed]
    public function overtimesSnapshot()
    {
        $query = $this->employee->overtimesSnapshot()->latest();

        if (filled($this->snapshotRef)) {
            $query->where('ref', 'like', '%' . trim($this->snapshotRef) . '%');
        }

        return $query->get();
    }


    public function toggleOvertimeArchives(): void
    {
        $this->showOvertimeArchives = !$this->showOvertimeArchives;
    }

    public function exportOvertimeArchives()
    {
        $rows = $this->overtimesSnapshot->map(fn ($snapshot) => [
            __('Ref') => $snapshot->ref,
            __('Semaine') => $snapshot->week,
            __('Type') => $snapshot->day_type?->label(),
            __('Heures') => $snapshot->hours,
            __('Taux horaire') => $snapshot->hours_rate,
            __('Alloc estimés') => $snapshot->alloc,
        ]);

        return new FastExcel($rows)->download('archives_heures_supp_' . $this->employee->id . '_' . now()->format('m_Y') . '.xlsx');
    }

    public function toggleFormOvertime(): void
    {
        $this->showOvertimeForm = !$this->showOvertimeForm;
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

        <flux:callout icon="information-circle" class="mt-5">
            <flux:callout.heading> Information</flux:callout.heading>
            <flux:callout.text>
                La base de calcul est égale au : (salaire catégoriel échelonné + diverses primes assimilées au salaire
                (prime de technicité, de rendement, de fonction))*nbres d'heures * pourcentage des heures supplémentaires.
                <flux:callout.link href="#">{{ __() }}</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
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




    <div class="mt-4 mb-2 flex items-center gap-2">
        <flux:button @click="activeForm = activeForm === 'archives-overtimes' ? null : 'archives-overtimes'" variant="filled">
            {{ __('Afficher les archives') }}
        </flux:button>
    </div>

    <x-container x-show="activeForm === 'archives-overtimes'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">Historique des heures supp. (snapshots)</flux:heading>
                <flux:text>Filtrez par ref (format m-Y).</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :label="__('Filtrer par ref')" :placeholder="__('ex: 05-2026')" />
                <flux:button wire:click="exportOvertimeArchives" icon="arrow-up-tray">{{ __('Exporter') }}</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Ref</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Semaine</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Type</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Heures</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Taux</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Alloc</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->overtimesSnapshot as $snapshot)
                <tr wire:key="ot-snapshot-{{ $snapshot->id }}">
                    <td class="px-6 py-4 text-sm">{{ $snapshot->ref }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->week }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->day_type?->label() }}</td>
                    <td class="px-6 py-4 text-sm">{{ $snapshot->hours }}</td>
                    <td class="px-6 py-4 text-sm">{{ number_format($snapshot->hours_rate, 0, ',', ' ') }} F cfa</td>
                    <td class="px-6 py-4 text-sm">{{ number_format($snapshot->alloc, 0, ',', ' ') }} F cfa</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8">Aucune heure supp. snapshot trouvée.</td>
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
