<?php

use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use Livewire\Component;

new class extends Component {
    public  $company;
    public $employees;
    public  $selectedEmployeeId;
    public $employee;

    public function mount($company)
    {
        $this->company = $company;
        $this->loadEmployees();
    }

    public function loadEmployees()
    {
        
        $this->employees = $this->company
            ->employeesWithPayslipStatus(StatusEnum::APPROVED->value)
           
            ->get();
        if ($this->employees->isNotEmpty()) {
            $this->selectEmployee($this->employees->first()->id);
        }
    }

    public function selectEmployee(string $employeeId)
    {
        $this->selectedEmployeeId = $employeeId;
        $this->employee = $this->employees->find($employeeId);
    }

    public function with(): array
    {
        $sal = [];
        $contributionsArray = [];

        if ($this->employee && $this->employee->payslip) {
            $sal = $this->employee->payslip->formatted_salaries;
            $contributionsArray = $this->employee->payslip->formatted_contributions;
        }

        return [
            'salaries' => $sal,
            'contributions' => $contributionsArray,
        ];
    }
}; ?>

<div>
    @if ($employees->isEmpty())
    <div class="flex flex-col items-center justify-center h-64 text-zinc-500">
        <svg class="w-16 h-16 mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-lg font-medium text-zinc-900 dark:text-white">Aucun bulletin de paie n'a encore été validé</p>
        <p class="text-sm">Veuillez générer et valider les bulletins dans la section <a href="{{ route('pay.check.payslips') }}" class="text-blue-500 underline">Valider les bulletins</a>.</p>
    </div>
    @else
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div>
            <flux:heading size="xl">{{ __('Payslips') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Preview of employee payslips') }}</flux:text>
        </div>
    </div>

    <div class="flex h-screen relative rounded-xl">
        {{-- Left sidebar for the list of employees --}}
        <div class="w-1/4 flex flex-col bg-zinc-900 border-r border-gray-200 h-full">
            <div class="p-4 border-b border-zinc-800">
                <h3 class="text-lg font-semibold mb-3 text-white">Employés</h3>
            </div>

            <!-- Employee List -->
            <div class="flex-1 overflow-y-auto p-2">
                <ul>
                    @foreach ($employees as $emp)
                    <li wire:key="employee-{{ $emp->id }}"
                        class="p-3 mb-2 rounded-lg cursor-pointer transition-colors {{ $selectedEmployeeId == $emp->id ? 'bg-zinc-700' : 'bg-zinc-800 hover:bg-zinc-700' }}"
                        wire:click="selectEmployee({{ $emp->id }})">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-zinc-600 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ substr($emp->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ $emp->name }}</p>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Right area for the payslip preview --}}
        <div class="w-3/4 p-4 overflow-y-auto relative bg-gray-50 dark:bg-zinc-950">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Bulletin de paie - {{ $employee ? $employee->name : '' }}
                </h3>
            </div>

            @if ($selectedEmployeeId && $employee && $employee->payslip)
            @include('pdf.payslip-content', [
            'employee' => $employee,
            'salaries' => $salaries,
            'contributions' => $contributions,
            ])
            @else
            <div class="flex flex-col items-center justify-center h-full text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium">Sélectionnez un employé</p>
                <p class="text-sm">Choisissez un employé dans la liste pour visualiser son bulletin.</p>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>