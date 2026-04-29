<?php

use App\Livewire\Forms\CompanyForm;
use App\Notifications\DeleteCompanyNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mettre à jour votre entreprise')] class extends Component {
    public CompanyForm $companyForm;

    public function mount()
    {
        if (!$this->company) {
            $this->redirect(route('settings.company.add'), navigate: true);
        }
        $this->companyForm->setCompany($this->company);
        $this->companyForm->isCreating = false;
    }
    #[Computed]
    public function company()
    {
        return auth()->user()->company;
    }

    public function update(): void
    {
        $updatedCompany = $this->companyForm->update();
        if ($updatedCompany):
            Flux::toast(variant: 'success', text: __('toast.updatecompanysuccess'));
            return;
        endif;
        Flux::toast(variant: 'danger', text: __('toast.companyfail'));
    }

    public function regenerateCompanyCode(): void
    {
        $regenCode = $this->companyForm->regenCompanyCode();
        if ($regenCode) {
            Flux::toast(variant: 'success', text: __('toast.companyregencode'));
            return;
        }
        Flux::toast(variant: 'warning', text: __('toast.companyregencodefail'));
    }

    public function deleteCompany(): void
    {
        Gate::authorize('delete', $this->company);
        auth()->user()->notify(new DeleteCompanyNotification(
                    company: $this->company,
                    user: auth()->user(),
                ));
        $deleteCompany = $this->company->delete();
        if ($deleteCompany) {



            Flux::toast(variant: 'success', text: __('toast.deletecompany'));
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }
    }
};
?>


<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('setting.settingupdateheading')" :subheading="__('setting.settingupdatesubheading')">

        <form wire:submit="update" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="companyForm.name" :label="__('setting.name')" type="text" />
                <flux:input wire:model="companyForm.email" :label="__('setting.email')" type="email" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="companyForm.phone" :label="__('setting.phone')" type="tel" />
                <flux:input wire:model="companyForm.adresse" :label="__('setting.adresse')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="companyForm.city" :label="__('setting.city')" type="text" />
                <flux:input wire:model="companyForm.niu" :label="__('setting.niu')" type="text" />
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <flux:input wire:model="companyForm.cnps" :label="__('setting.cnps')" type="text" />
                <flux:input wire:model="companyForm.rccm" :label="__('setting.rccm')" type="text" />
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('setting.settingupdateheading') }}
                </flux:button>
            </div>
        </form>
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 mt-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <flux:heading level="1" size="lg">
                        {{ __('setting.dangerzone') }}
                    </flux:heading>
                    <flux:text>
                        {{ __('setting.regencodesubtitle') }}
                    </flux:text>
                </div>

            </div>
            <flux:button wire:click="regenerateCompanyCode">
                {{ __('setting.regencodebutton') }}
            </flux:button>
        </div>
        <div class="flex items-center justify-between gap-2 mt-10">
            <div>
                <flux:heading level="1" size="lg" class="font-extrabold">
                    {{ __('setting.deletecompanyheading') }}</flux:heading>
                <flux:text class="text-gray-300">
                    {{ __('setting.deletecompanysubheading') }}
                </flux:text>
            </div>

            <flux:modal.trigger name="delete-company">
                <flux:button variant="danger">
                    {{ __('setting.deletebutton') }}
                </flux:button>
            </flux:modal.trigger>
        </div>


        <flux:modal name="delete-company" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">
                        {{ __('setting.deletecompanyheading') }}</flux:heading>
                    <flux:subheading>
                        {{ __('setting.deletecompanysubheading') }}
                    </flux:subheading>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('setting.cancelbutton') }}</flux:button>
                    </flux:modal.close>

                    <flux:button wire:click="deleteCompany" variant="danger">
                        {{ __('setting.confirmdeletion') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </x-settings.layout>

</section>
