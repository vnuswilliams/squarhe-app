<?php

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Livewire\Forms\EmployeeRemunerationForm;
use App\Models\Remuneration;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $employee;

    public EmployeeRemunerationForm $form;

    public $showRemunerationForm = false;

    public function mount($employee)
    {
        $this->employee = $employee;
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
        $this->showRemunerationForm = false;
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
        $this->showAvgForm = false;
    }

    public $showAvgForm = false;

    public function toggleRemunerationForm(): void
    {
        $this->showAvgForm = false;
        $this->showRemunerationForm = ! $this->showRemunerationForm;
    }

    public function toggleAvgSalary()
    {
        $this->showRemunerationForm = false;
        $this->showAvgForm = ! $this->showAvgForm;
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-4">
        <div>
            <flux:heading level="1" class="font-bold"> Éléments de rémunération </flux:heading>
            <flux:text class="text-gray-300">Primes, retenues, et autres variables de paie appliqués a cet employé.</flux:text>
        </div>

        <div class="flex items-center gap-2">

            <flux:button wire:click="toggleRemunerationForm" variant="primary">
                Ajouter un élément
            </flux:button>
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item wire:click="toggleAvgSalary">
                        {{ __('Add average salary') }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="toggleImportRenum">
                        Importer des éléments
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

        </div>

    </div>

    @if ($showRemunerationForm)
    <x-container wire:transition>
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
                <flux:button type="button" wire:click="toggleRemunerationForm">Annuler</flux:button>
                <flux:button type="submit" variant="primary">
                    Enregistrer
                </flux:button>
            </div>
        </form>
    </x-container>
    @endif


    @if($showAvgForm)
    <x-container wire:transition>
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
                <flux:button wire:click="toggleAvgSalary"> {{ __('Cancel') }} </flux:button>
                <flux:button type="submit" variant="primary">Ajouter</flux:button>

            </div>
        </form>
    </x-container>
    @endif

    {{-- Delta Card for Remuneration --}}
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