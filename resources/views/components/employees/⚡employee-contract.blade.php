<?php

use App\Enums\ContractTypeEnum;
use App\Livewire\Forms\EmployeeForm;
use App\Models\ContractArchive;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    //
    public $employee;
    public EmployeeForm $form;
    public $showAddContractForm = false;
    public $showNewContractForm = false; // 🔹 Nouveau

    public function mount()
    {
        $this->form->setContract($this->employee);
        
    }

    public function toggleFormEditContract()
    {
        $this->createNewContract = false;
        $this->showAddContractForm = !$this->showAddContractForm;
    }

    public $createNewContract = false;
    public function toggleFormNewContract()
    {
        $this->createNewContract = !$this->createNewContract;
        $this->showAddContractForm = !$this->showAddContractForm;
    }
    public function update()
    {
        $this->form->isCreating = false;
        if ($this->createNewContract):
            Gate::authorize('create', ContractArchive::class);
            $this->employee->contractArchives()->create([
                'motif' => 'New contract',
                'department' => $this->employee->department,
                'job_title' => $this->employee->job_title,
                'contract_type' => $this->employee->contract_type,
                'end_date' => $this->employee->end_date,
                'start_date' => $this->employee->start_date,
                'base_salary' => $this->employee->base_salary,
                'category' => $this->employee->data['category'],
                'smic' => $this->employee->data['smic'],
                'average_salary' => $this->employee->data['average_salary'],
            ]);
        endif;

        $this->form->update();
        $name = $this->employee->shortName;
        $this->showAddContractForm = false; // 🔹 ferme le formulaire d’update
        if ($this->createNewContract):
            Flux::toast(variant: 'success', text: "Nouveau contrat crée et l' ancien archivé  avec succès.");
            return;
        endif;
        Flux::toast(variant: 'success', text: "Les infos. contractuelles de $name ont été mises à jour.");
    }


    public $archiveToDelete = null;
    public function confirmBeforeDelete($idContractArchiveWeWantToDelete)
    {
        $this->archiveToDelete = ContractArchive::whereId($idContractArchiveWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-contractArchive-modal')->show();
    }
    public function delete()
    {
        if ($this->archiveToDelete):
            Gate::authorize('delete', [ContractArchive::class, $this->archiveToDelete]);
            $this->archiveToDelete->delete();
            Flux::toast(variant: 'success', text: "L' archive du contrat a été supprimé avec succès.");
            Flux::modal('delete-contractArchive-modal')->close();
            $this->archiveToDelete = null;
        endif;
    }
};
?>

<div>

    <div class="flex items-center justify-between mb-4">
        <div>
            <flux:heading level="1" class="font-bold"> {{ __('Elements contractuels') }}</flux:heading>
            <flux:text class="text-gray-300">{{ __('Gérez et consulter le/les contrats actuel ou archives de votre collaborateur.') }}</flux:text>
        </div>
        <div class="flex items-center gap-2">

            <flux:button tooltip="Editer le contrat existant" wire:click="toggleFormEditContract" variant="primary" icon="pencil" />
            <flux:button tooltip="Ajouter un nouveau contrat" wire:click="toggleFormNewContract" icon="document-plus" />
            <flux:button href="{{ route('employees.end.contract', ['employee' => $this->employee] ) }}" tooltip="Rupture ou suspension de contrat" icon="arrow-right-start-on-rectangle" variant="danger"  />
        </div>
    </div>

    @if ($showAddContractForm)
    <x-container wire:transition>
        @if($createNewContract) 
    <div class="mb-4">
            <flux:heading level="1" class="font-bold"> 
            Modifier le contrat 
            
        </flux:heading>
        <flux:text class="text-gray-300">{{ __('Le présent contrat sera modifier.') }}</flux:text>
    </div>
    @else  
    <div class="mb-4">
            <flux:heading level="1" class="font-bold"> 
            Ajouter un nouveau contrat 
            
        </flux:heading>
        <flux:text class="text-gray-300">{{ __('Le présent contrat sera modifier et archiver, votre collaborateur sera averti de son nouveau contrat.') }}</flux:text>
    </div>
    @endif

        <form wire:submit="update" class="space-y-6" id="add-document-form" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Department -->
                <flux:input id="department" wire:model="form.department" type="text" :label="__('department')" />

                <!-- Job Title -->
                <flux:input id="job_title" wire:model="form.job_title" type="text" :label="__('job.title')" />

                <!-- Contract Type -->
                <flux:select id="contract_type" wire:model="form.contract_type" :label="__('type.contract')">
                    @foreach (ContractTypeEnum::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->name }}</option>
                    @endforeach
                </flux:select>

                <!-- Start Date -->
                <flux:input id="start_date" wire:model="form.start_date" type="date" :label="__('start.date')" />

                <!-- End Date -->
                <flux:input id="end_date" wire:model="form.end_date" type="date" :label="__('end.date')" />

                <!-- Base Salary -->
                <flux:input id="base_salary" wire:model="form.base_salary" type="number" step="0.01"
                    :label="__('base.salary')" />
                <!-- Average salary -->
                <flux:input id="average_salary" wire:model="form.average_salary" type="number"
                    :label="__('Salaire moyen (Optionnel')" />

                <!-- Smic -->
                <flux:input id="smic" wire:model="form.smic" type="number"
                    :label="__('Smic (Optionnel')" />

                <!-- Professional Category -->
                <flux:input id="category" wire:model="form.category" type="text"
                    :label="__('category')" />
            </div>
            {{-- Bouton d’enregistrement --}}
            <div class="flex items-center justify-end gap-4">
                <flux:button wire:click="toggleFormEditContract">
                    {{ __('cancel') }}
                </flux:button>
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}

                </flux:button>

            </div>
        </form>
    </x-container>
    @endif

     <x-container>
        <flux:heading level="2" class="font-bold mb-4">{{ __('Contrat actuel') }}</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('Département') }}</flux:text>
                    <flux:text class="font-medium">{{ $employee->department?->label() ?? 'N/A' }}</flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('Intitulé du poste') }}</flux:text>
                <flux:text class="text-gray-200">{{ $employee->job_title }}</flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('Type de contrat') }}</flux:text>
                <flux:text class="text-gray-200">
                    {{ $employee->contract_type?->label() }}
                </flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold"> {{ __('Date de début') }} </flux:text>
                <flux:text class="text-gray-200">
                    {{ $employee->start_date->translatedFormat('d M Y') }}
                </flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('Date de fin') }}</flux:text>
                <flux:text class="text-gray-200">
                                     {{ $employee->end_date?->translatedFormat('d M Y') ?? '-'}}
                </flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('Salaire de base') }}</flux:text>
                <flux:text class="text-gray-200">
                    {{ number_format($employee->base_salary, 2, '', ' ') }} F cfa
                </flux:text>
            </div>
            <div>
                <flux:text class="text-gray-300 font-semibold">{{ __('categorie socio pro.') }}</flux:text>
                <flux:text class="text-gray-200">
                    {{ $employee->data['category'] ?? '-' }}
                </flux:text>
            </div>
        </div>
    </x-container>


    @if($this->employee->contractArchives->isNotEmpty())
     <div>
            <flux:heading level="1" class="font-bold"> {{ __('Contrat archivé(s)') }}</flux:heading>
            <flux:text class="text-gray-300">{{ __('Consulter le(s) contrat(s) passé(s) de votre collaborateur.') }}</flux:text>
        </div>
    <x-container>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Poste') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de début') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de fin') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Ajouter par') }}
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($this->employee->contractArchives as $contratArchive)
                <tr wire:key="{{ $contratArchive->id }}">

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $contratArchive->job_title }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $contratArchive->motif }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $contratArchive->contract_type }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($contratArchive->start_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($contratArchive->end_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $contratArchive->added_by }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">

                            <flux:button wire:click="confirmBeforeDelete({{ $contratArchive->id }})"
                                size="sm"
                                variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucun contrat archivé pour ').$this->employee->name }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </x-container>
    @endif


    <flux:modal name="delete-contractArchive-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce contrat ?</flux:heading>
            </div>
            <p>
                Voulez vous vraiment supprimer cet archive de contrat ??
            </p>
            <p>Cette action est irréversiblee.</p>

            <div class="flex justify-end gap-2  pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>

                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
