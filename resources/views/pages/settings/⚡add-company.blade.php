<?php

use App\Livewire\Forms\AddCompanyForm;
use App\Livewire\Forms\UpdateCompanyForm;
use Livewire\Attributes\Title;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new #[Title('Ajouter une entreprise')] class extends Component
{
    public AddCompanyForm $addCompany;
    public UpdateCompanyForm $updateCompany;

public $company;
    public function mount()
    {
        $this->company = auth()->user()->company()->first();

    
        if ($this->company) {
            $this->updateCompany->setCompany($this->company);
        }
    }

    public function save(): void
    {
        $this->addCompany->store();
        Flux::toast(variant: 'success', text: __('Votre compagnie a été créée avec succès'));
               $this->redirect(route('dashboard'), navigate: true);

    }

    public function update(): void
    {
        $this->updateCompany->update();
        Flux::toast(variant: 'success', text: __('Votre compagnie a été mise à jour avec succès'));
    }

    public function regenerateCompanyCode(): void
    {
        $this->company->update([
            'company_code' => Str::random(10)
        ]);
        Flux::toast(variant: 'success', text: __('Le code de l\'entreprise a été régénéré'));
    }

    public function deleteCompany(): void
    {
        $this->company->delete();
        Flux::toast(variant: 'success', text: __('L\'entreprise a été supprimée'));
        $this->redirect(route('dashboard'), navigate: true);
    }
};
?>


<section class="w-full">
    @include('partials.settings-heading')
    @if(!$company)

    <x-settings.layout :heading="__('Add Company')" :subheading="__('Add a new company to the system')">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="addCompany.name" placeholder="Squarhe" :label="__('Name *')" type="text" />
                <flux:input wire:model="addCompany.email" placeholder="contact@squarhe.com" :label="__('Email*')" type="email" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="addCompany.phone" placeholder="659005679" :label="__('Phone *')" type="tel" />
                <flux:input wire:model="addCompany.adresse" placeholder="Bonaberi, cameroun" :label="__('Adresse *')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="addCompany.city" placeholder="Douala" :label="__('City *')" type="text" />
                <flux:input wire:model="addCompany.nui" :label="__('N° NUI')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="addCompany.cnps" :label="__('N° CNPS')" type="text" />
                <flux:input wire:model="addCompany.rccm" :label="__('N° RCCM')" type="text" />
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Save Company') }}
                </flux:button>


            </div>
        </form>
    </x-settings.layout>
    @else

    <x-settings.layout :heading="__('Update Company')" :subheading="__('Update your company infos')">

        <form wire:submit="update" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="updateCompany.name" placeholder="{{ $this->company->name }}" :label="__('Name *')" type="text" />
                <flux:input wire:model="updateCompany.email" placeholder="{{ $this->company->email }}" :label="__('Email*')" type="email" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="updateCompany.phone" placeholder="{{ $this->company->phone }}" :label="__('Phone *')" type="tel" />
                <flux:input wire:model="updateCompany.adresse" placeholder="{{ $this->company->adresse }}" :label="__('Adresse *')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="updateCompany.city" placeholder="{{ $this->company->city }}" :label="__('City *')" type="text" />
                <flux:input wire:model="updateCompany.nui" placeholder="{{ $this->company->nui }}" :label="__('N° NUI')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="updateCompany.cnps" placeholder="{{ $this->company->cnps }}" :label="__('N° CNPS')" type="text" />
                <flux:input wire:model="updateCompany.rccm" placeholder="{{ $this->company->rccm }}" :label="__('N° RCCM')" type="text" />
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Mettre à jour') }}
                </flux:button>
            </div>
        </form>
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 mt-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-medium text-red-600 dark:text-red-500">{{ __('Zone de danger') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ __('Si vous pensez que le code unique de votre entreprise a été corompu vous pouvez le changer.') }}
                    </p>
                </div>

            </div>
            <flux:button wire:click="regenerateCompanyCode">
                {{ __('Regenerate company code') }}
            </flux:button>
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 mt-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-medium text-red-600 dark:text-red-500">{{ __('Zone de danger') }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ __('La suppression de votre entreprise est irréversible. Toutes les données associées seront perdues.') }}
                    </p>
                </div>

                <flux:modal.trigger name="delete-company">
                    <flux:button variant="danger">
                        {{ __('Supprimer l\'entreprise') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>


        <flux:modal name="delete-company" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">{{ __('Supprimer l\'entreprise ?') }}</flux:heading>
                    <flux:subheading>
                        {{ __('Êtes-vous sûr de vouloir supprimer cette entreprise ? Cette action est irréversible.') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Annuler') }}</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="deleteCompany" variant="danger">
                        {{ __('Confirmer la suppression') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-settings.layout>
    @endif

</section>