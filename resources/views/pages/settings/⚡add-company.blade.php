<?php

use App\Enums\PlanEnum;
use App\Livewire\Forms\CompanyForm;
use App\Services\SubscriptionService;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ajouter une entreprise')] class extends Component {
    public CompanyForm $companyForm;

    public function mount()
    {
        if ($this->company) {
            $this->redirect(route('settings.company.update'), navigate: true);
        }
    }
    #[Computed]
    public function company()
    {
        return auth()->user()?->company;
    }

    public function save(): void
    {
        $companyCreated = $this->companyForm->create();
        if ($companyCreated):
            app(SubscriptionService::class)->subscribeTo($companyCreated, PlanEnum::FREE);
            Flux::toast(variant: 'success', text: __('toast.companycreationsucces'));
            $this->redirect(route('settings.company.setting'), navigate: true);
            $this->reset();
            return;
        endif;
        Flux::toast(variant: 'danger', text: __('toast.companyfail'));
    }
};
?>


<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout :heading="__('setting.settingaddheading')" :subheading="__('setting.settingaddsubheading')">
       <form wire:submit="save" class="my-6 w-full space-y-6">
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
                    {{ __('setting.settingaddheading') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>


</section>
