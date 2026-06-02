<?php
use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\DepartmentEnum;
use App\Enums\NationalityEnum;
use App\Livewire\Forms\EmployeeForm;
use App\Services\SubscriptionService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ajouter un employé')] class extends Component
{
    public EmployeeForm $form;

    #[Computed]
    public function company()
    {
        return auth()->user()?->company()->first();
    }

    public function save()
    {
        $this->form->average_salary = (empty($this->form->average_salary) || $this->form->average_salary === null || $this->form->average_salary === 0) ? $this->form->base_salary : $this->form->average_salary;
        $this->form->smic = (empty($this->form->smic) || $this->form->smic === null || $this->form->smic === 0) ? $this->form->base_salary : $this->form->smic;

        $employee = $this->form->create();

        Flux::toast(variant: 'success', text: " L' employé(e) a été crée(e) avec succès");
        if ($this->company) {
            app(SubscriptionService::class)->consumeEmployeeSlot($this->company);
        }
        $this->redirect(route('employees.show', ['id' => $employee->id]), navigate: true);
    }
};
?>

<section class="w-full space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('add.employee') }}</flux:heading>
            <flux:text variant="subtle">{{ __('employee.subtitle') }} </flux:text>
        </div>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item href="{{ route('employees') }}">{{ __('Employé') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('add.employee') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>
    @if($this->company)

    <form wire:submit="save" class="space-y-8">
        <!-- EMPLOYEE DETAILS -->
        <div
            class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md p-6 rounded-2xl  border border-zinc-100 dark:border-zinc-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                {{ __('Employee Details') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Civility -->
                <flux:select id="civility" wire:model="form.civility" :label="__('Civility')">
                    <flux:select.option value=""> Choisir une option</flux:select.option>
                    @foreach (CivilityEnum::options() as $case)
                    <flux:select.option value="{{ $case['value'] }}">{{ $case['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <!-- Name -->
                <flux:input id="name" wire:model="form.name" type="text" :label="__('Full Name')" />

                <!-- Email -->
                <flux:input id="email" wire:model="form.email" type="email" :label="__('Email Address')" />

                <!-- Phone -->
                <flux:input id="phone" wire:model="form.phone" type="text" :label="__('Phone (9 digits)')" />

                <!-- Birth Date -->
                <flux:input id="birth_date" wire:model="form.birth_date" type="date" :label="__('Birth Date')" />

                <!-- Nationality -->
                <flux:select id="nationality" wire:model="form.nationality" :label="__('Nationality')">
                    <flux:select.option value=""> Choisir une option</flux:select.option>
                    @foreach (NationalityEnum::options() as $case)
                    <flux:select.option value="{{ $case['value'] }}">{{ $case['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <!-- Number of Children -->
                <flux:input id="child" wire:model="form.child" type="number" min="0"
                    :label="__('Number of Children')" />

                <!-- NIU -->
                <flux:input id="niu" wire:model="form.niu" type="text" :label="__('NIU')" />

                <!-- CNPS -->
                <flux:input id="cnps_number" wire:model="form.cnps_number" type="text" :label="__('CNPS Number')" />
            </div>
        </div>

        <!-- CONTRACT DETAILS -->
        <div
            class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md p-6 rounded-2xl shadow-md border border-zinc-100 dark:border-zinc-800">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                
                {{ __('Contract Details') }}
            </h2>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<!-- Department -->
<flux:select id="department" wire:model="form.department" :label="__('Départment')">
                    <flux:select.option value=""> Choisir une option</flux:select.option>
                    @foreach (DepartmentEnum::options() as $case)
                    <flux:select.option value="{{ $case['value'] }}">{{ $case['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

   <!-- Job Title -->
   <flux:input id="job_title" wire:model="form.job_title" type="text" :label="__('Job Title')" />
   <!-- Contract Type -->
                <flux:select id="contract_type" wire:model="form.contract_type" :label="__('Contract Type')">
                    <flux:select.option value=""> Choisir une option</flux:select.option>
                    @foreach (ContractTypeEnum::options() as $case)
                    <flux:select.option value="{{ $case['value'] }}">{{ $case['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
   
                <!-- Start Date -->
                <flux:input id="start_date" wire:model="form.start_date" type="date" :label="__('Start Date')" />

                <!-- End Date -->
                <flux:input id="end_date" wire:model="form.end_date" type="date" :label="__('End Date (optional)')" />

<!-- Base Salary -->
<flux:input id="base_salary" wire:model.blur="form.base_salary" type="number"
    :label="__('Base Salary')" />


<!-- Average salary -->
<flux:input id="average_salary" wire:model="form.average_salary" type="number"
    :label="__('Salaire moyen (Optionnel)')" />

<!-- Smic -->
<flux:input id="smic" wire:model="form.smic" type="number"
    :label="__('Smic (Optionnel)')" />

<!-- Professional Category -->
<flux:input id="category" wire:model="form.category" type="text"
    :label="__('Professional Category (optional)')" />
        </div>
        </div>

        <!-- ACTIONS -->
        <div class="flex items-center justify-end gap-4">
            <flux:button type="submit" variant="primary" class="px-6 py-2">
                {{ __('Save Employee') }}
            </flux:button>
        </div>
    </form>
    @else
    <x-no-company />
    @endif
</section>