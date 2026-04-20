<?php

use App\Enums\ContractTypeEnum;
use App\Jobs\CalculatePayslipJob;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $uuid;
    public $id, $motif;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
    }
     public function showPayslipModal()
    {
        CalculatePayslipJob::dispatch($this->employee);
        Flux::modal('payslip-modal')->show();
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.payslip', ['employee' => $this->employee]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'payslip.pdf');
    }

      public function with()
    {
        $sal = [];
        $contributionsArray = [];
        if ($this->employee && $this->employee->payslip) {
            $sal = $this->employee->payslip->formatted_salaries;
            $contributionsArray = $this->employee->payslip->formatted_contributions;
        }

        return [
            'employee' => $this->employee,
            'salaries' => $sal,
            'contributions' => $contributionsArray,
        ];
    }
    #[Computed]
    public function employee()
    {
        return Employee::where('uuid', $this->uuid)
            ->with(['contractArchives', 'leaves'])
            ->first();
    }//

};
?>

<div>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('employees') }}">{{ __('Employé') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ Str::limit($this->employee->name, 10, '.') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    <div class="flex items-center justify-between gap-4 mb-16">
        <div class="flex items-center justify-start gap-4">
            <flux:avatar name="{{ $this->employee->name }}" />
            <div>
                <flux:heading level="1">{{ $this->employee->name }}</flux:heading>
                <flux:text>{{ $this->employee->job_title . ' . ' . $this->employee->department }}</flux:text>
            </div>
        </div>
        <div class="flex items-center gap-4">

            <flux:button variant="primary" wire:click="showPayslipModal">Voir le bulletin</flux:button>
        </div>
    </div>
<x-tabs :tabs="[
        'Rémunération.',
        'Contrat',
        'Absences',
        'Heures supp.',
        'Documents',
        'Impôts',
        'Réglages',
    ]">
        <x-slot:tab1>

        </x-slot:tab1>
        <x-slot:tab2>

        </x-slot:tab2>
        <x-slot:tab3>
            <livewire:employees.employee-leaves :employee="$this->employee"/>
        </x-slot:tab3>

        <x-slot:tab4>

        </x-slot:tab4>

        <x-slot:tab5>
        </x-slot:tab5>

        <x-slot:tab6>
        </x-slot:tab6>
        <x-slot:tab7>

        </x-slot:tab7>


    </x-tabs>
    
    <flux:modal name="payslip-modal" class="min-w-[900px]">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Bulletin de {{ $this->employee->name }}</flux:heading>
            </div>
            <div class="container mx-auto p-4 max-w-4xl">
                @if ($this->employee->payslip && $this->employee->contract_type != ContractTypeEnum::INTERNSHIP)
                @include('pdf.payslip-content', [
                'employee' => $this->employee,
                'salaries' => $salaries,
                'contributions' => $contributions,
                ])
                @elseif (!$this->employee->payslip)
                <div wire:poll.visible="showPayslipModal"
                    class="flex flex-col items-center justify-center h-full p-8 text-zinc-500">
                    <div
                        class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-800">
                        <svg class="h-8 w-8 animate-spin text-blue-600 dark:text-blue-500"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-lg font-semibold text-zinc-900 dark:text-white">Génération du bulletin de
                                paie...</span>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">Cette opération peut prendre
                                quelques
                                instants. La boîte de dialogue se mettra à jour automatiquement.</span>
                        </div>
                    </div>
                </div>
                @elseif($this->employee->contract_type === ContractTypeEnum::INTERNSHIP)
                <flux:text>{{ $this->employee->name.' est un stagiaire, et ne peut avoir un bulletin de paie.' }}</flux:text>
                @endif
            </div>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="primary" wire:click="downloadPdf" icon="arrow-down-tray">
                    Télécharger
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>