<?php

use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{

    public $activeEmployeesCount = 0;
    public $onLeaveEmployeesCount = 0;
    public $contractsEndingThisMonthCount = 0;

    public $expiredContracts = [];
    public $expiringContracts = [];
    public $trialEndingContracts = [];
    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->company && !$this->company->employees) {
            return;
        }

        // Effectif total (basé sur le statut actif/pending comme défini dans Company)
        $this->activeEmployeesCount = $this->company->employees->where('status', '!=', StatusEnum::TERMINATED->value)->count();

        // Employés en congés
        $this->onLeaveEmployeesCount = $this->company->employees->where('status', StatusEnum::ONLEAVE->value)->count();

        $now = Carbon::now();

        // Contrats qui se terminent ce mois-ci
        $thiscontractEndingThisMonthCount = $this->company
            ->employees()
            ->whereMonth('end_date', $now->month)->whereYear('end_date', $now->year)
            ->count();

        // Contrats expirés
        $this->expiredContracts = $this->company
            ->employees
            ->where('end_date', '<', $now)            ;

        // Contrats expirant dans les 30 prochains jours
        $in30Days = now()->addDays(30);
        $this->expiringContracts = $this->company
            ->employees
            ->where('end_date', '>', $now)->where('end_date', '<=', $in30Days) ;

        // Périodes d'essai (type 'essay') finissant dans les 30 prochains jours
        $this->trialEndingContracts = $this->company
            ->employees
            ->where('contract_type', ContractTypeEnum::ESSAY)
            ->where('end_date', '>', $now)->where('end_date', '<=', $in30Days)         ;
    }
    #[Computed]
    public function company()
    {
        return  auth()->user()->company()->with('employees')->first();
    }
};
?>

<section class="w-full space-y-6">

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
                {{-- <flux:menu>

                    <flux:menu.item href="{{ route('import.remuneration') }}" wire:navigate>
                {{ __('Importer les elements de remunerations des employés') }}
                </flux:menu.item>
                <flux:menu.item href="{{ route('import.leaves') }}" wire:navigate>
                    {{ __('Importer les conges et absences des employés') }}
                </flux:menu.item>
                <flux:menu.item href="{{ route('import.overtime') }}" wire:navigate>
                    {{ __('Importer les heures supp des employés') }}
                </flux:menu.item>
                </flux:menu>--}}
            </flux:dropdown>
        </div>
    </div>
    @if($this->company)

    <div class="flex flex-col w-full gap-4 md:flex-row">
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
                        <flux:button href="{{ route('employees.show', ['employee' => $employee]) }}" wire:navigate
                            variant="primary" size="sm" icon="eye">
                        </flux:button>
                    </div>
                    @endforeach
                </div>
            </flux:callout>
        </div>
        @endif

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
                        <flux:button href="{{ route('employees.show', ['employee' => $employee]) }}" wire:navigate
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
                        <flux:button href="{{ route('employees.show', ['employee' => $employee]) }}" wire:navigate
                            variant="primary" size="sm" icon="eye">
                        </flux:button>
                    </div>
                    @endforeach
                </div>
            </flux:callout>
        </div>
        @endif
    </div>

    <x-tabs :tabs="['Tous les employés', 'Vue d\'ensemble', 'Livre de paie', 'Déclarations', 'Archives']">
        <x-slot:tab1>
           {{-- @livewire('employees.list-employee', ['company' => $company]) --}}
        </x-slot:tab1>
        <x-slot:tab2>
        </x-slot:tab2>

        <x-slot:tab3>

        </x-slot:tab3>

        <x-slot:tab4>
        </x-slot:tab4>

        <x-slot:tab5>
        </x-slot:tab5>
    </x-tabs>

    @else
    <x-no-company />
    @endif
</section>