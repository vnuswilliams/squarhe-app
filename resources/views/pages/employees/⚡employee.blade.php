<?php

use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new  #[Title('Tous les employés')] class extends Component
{

    public int $activeEmployeesCount = 0;
    public int $onLeaveEmployeesCount = 0;
    public int $contractsEndingThisMonthCount = 0;

    public  $expiredContracts = [];
    public  $expiringContracts = [];
    public  $trialEndingContracts = [];
    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->company && !$this->company?->employees) {
            return;
        }

        // Effectif total (basé sur le statut actif/pending comme défini dans Company)
        $this->activeEmployeesCount = $this->company->employees->where('status', '!=', StatusEnum::TERMINATED->value)->count();

        // Employés en congés
        $this->onLeaveEmployeesCount = $this->company->employees->where('status', StatusEnum::ONLEAVE->value)->count();

        $now = Carbon::now();

        // Contrats qui se terminent ce mois-ci
        $this->contractsEndingThisMonthCount = $this->company
            ->employees()
            ->whereMonth('end_date', $now->month)->whereYear('end_date', $now->year)
            ->count();

        // Contrats expirés
        $this->expiredContracts = $this->company
            ->employees
            ->where('end_date', '<', $now);

        // Contrats expirant dans les 30 prochains jours
        $in30Days = now()->addDays(30);
        $this->expiringContracts = $this->company
            ->employees
            ->where('end_date', '>', $now)->where('end_date', '<=', $in30Days);

        // Périodes d'essai (type 'essay') finissant dans les 30 prochains jours
        $this->trialEndingContracts = $this->company
            ->employees
            ->where('contract_type', ContractTypeEnum::ESSAY)
            ->where('end_date', '>', $now)->where('end_date', '<=', $in30Days);
    }
    #[Computed]
    public function company()
    {
        return  auth()->user()->company()->with('employees')->first();
    }
    public function delete(string $id)
    {
        $employee = Employee::whereId($id)->firstOrFail();
        Gate::authorize('delete', [Employee::class, $employee]);
        $employee->delete();
    }
};
?>

<div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('employee.title') }}</flux:heading>
            <flux:text variant="subtle">{{ __('employee.subtitle') }} </flux:text>
        </div>
        <div>
            <flux:button variant="primary" icon="user-plus" href="{{ route('employees.add') }}" wire:navigate />
            <flux:button icon="user-group" href="{{ route('employees.import') }}" wire:navigate />
            <flux:dropdown>
                <flux:button icon="bars-3" />
            </flux:dropdown>
        </div>
    </div>
    @if($this->company)

    <x-delta-card :cards="[
        [
            'label' => 'Effectif total',
            'current' => $this->activeEmployeesCount,
            'delta' => '',
            'color' => 'blue',
        ],
        [
            'label' => 'Fin de contrat (mois)',
            'current' => $this->contractsEndingThisMonthCount,
            'delta' => '',
            'color' => 'amber',
        ],
        [
            'label' => 'Contrats expirés',
            'current' => $expiredContracts->count(),
            'delta' => '',
            'color' => 'rose',
        ],
        [
            'label' => 'En congés',
            'current' => $this->onLeaveEmployeesCount,
            'delta' => '',
            'color' => 'emerald',
        ],
    ]" />


    <x-tabs :tabs="['Vue d\' ensemble', 'Tous les employés',  'Fin de contrat', 'Contrats expirés', 'En congés']">
       

        
        <x-slot:tab2>
        </x-slot:tab2>

        <x-slot:tab3>
        @if ($expiringContracts->isNotEmpty())
        <div class="flex-1">
            <flux:callout variant="warning" icon="clock" title="Expire dans moins de 30 jours">
                <p class="mb-2 text-sm opacity-70">
                    Ces contrats expirent bientôt. Pensez à préparer les renouvellements.
                </p>
                <div class="flex flex-col gap-2 mt-2">
                    @foreach ($expiringContracts as $employee)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white/10">
                        <span class="font-medium">{{ $employee->name }}</span>
                        <flux:button href="{{ route('employees.show',  ['id' => $employee->id]) }}" wire:navigate
                            variant="primary" size="sm" icon="eye">
                        </flux:button>
                    </div>
                    @endforeach
                </div>
            </flux:callout>
        </div>
        @endif
        @if ($trialEndingContracts->isNotEmpty())
        <div class="flex-1">
            <flux:callout variant="info" icon="information-circle" title="Fin de période d'essai">
                <p class="mb-2 text-sm opacity-70">
                    La période d'essai arrive à son terme. Une décision est attendue.
                </p>
                <div class="flex flex-col gap-2 mt-2">
                    @foreach ($trialEndingContracts as $employee)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white/10">
                        <span class="font-medium">{{ $employee->name }}</span>
                        <flux:button href="{{ route('employees.show',  ['id' => $employee->id]) }}" wire:navigate
                            variant="primary" size="sm" icon="eye">
                        </flux:button>
                    </div>
                    @endforeach
                </div>
            </flux:callout>
        </div>
        @endif
        </x-slot:tab3>

        <x-slot:tab4>
        @if ($expiredContracts->isNotEmpty())
        <div class="flex-1">
            <flux:callout variant="danger" icon="exclamation-triangle" title="Contrats Expirés">
                <p class="mb-2 text-sm opacity-70">
                    Ces contrats sont arrivés à terme. Veuillez régulariser la situation.
                </p>
                <div class="flex flex-col gap-2 mt-2">
                    @foreach ($expiredContracts as $employee)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-white/10">
                        <span class="font-medium">{{ $employee->name }}</span>
                        <flux:button href="{{ route('employees.show', ['id' => $employee->id]) }}" wire:navigate
                            variant="primary" size="sm" icon="eye">
                        </flux:button>
                    </div>
                    @endforeach
                </div>
            </flux:callout>
        </div>
        @endif
        </x-slot:tab4>

        <x-slot:tab5>
        </x-slot:tab5>
         <x-slot:tab1>
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
                            {{ __('Poste') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                            {{ __('Département') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                            {{ __('Type de contrat') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                            {{ __('Statut') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                                {{ __('Actions') }}
                            </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">

                    @foreach ($this->company->employees as $employee)
                    <tr wire:key="{{ $employee->id }}">

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">

                            <div class="flex items-center gap-3">
                                <flux:avatar name="{{ $employee->name }}" size="sm" />
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $employee->name }}</span>
                            </div>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                            {{ $employee->job_title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                            {{ $employee->department }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">

                            <flux:badge size="sm" color="green">
                                {{ App\Enums\ContractTypeEnum::from($employee->contract_type)->label() }}
                            </flux:badge>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">

                            <flux:badge size="sm" color="{{ $employee->status?->color()}}">
                                {{ $employee->status?->label() }}
                            </flux:badge>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                            <flux:button href="{{ route('employees.show', ['id' => $employee->id]) }}" wire:navigate variant="ghost" size="sm" icon="eye" />
                            <flux:button wire:click="delete('{{ $employee->id }}')" wire:navigate variant="ghost" size="sm" icon="trash" />

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </x-container>
        </x-slot:tab1>
    </x-tabs>

    @else
    <x-no-company />
    @endif
</div>