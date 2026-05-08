<?php

use App\Enums\StatusEnum;
use App\Jobs\GeneratePayrollBookJob;
use App\Models\PayrollBook;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{  
    
public $company;
    public ?PayrollBook $payrollBook = null;
    public bool $isProcessing = true;

    // Properties to hold the data
    public array $employees = [];
    public array $listEmployee = [];
    public array $matrix = [];
    public array $employeeContribution = [];
    public array $employerContribution = [];
    public array $retenues = [];
    public array $salariesDetails = [];
    public bool $validatePayslip = false;

    public int $currentPage = 1;
    public int $perPage = 5;

    public function nextPage()
    {
        $this->currentPage++;
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) $this->currentPage--;
    }

    #[Computed()]
    public function payrollBook()
    {
return        $this->company->payrollBook;
    }

   
    public function refreshPayrollBook()
    {
        $this->company->payrollBook()->delete();
        Flux::toast(variant: 'success', text: 'Le livre de paie sera généré.');
    }
    protected function populateDataFromPayrollBook(): void
    {
        $data = $this->payrollBook->data;

        $this->listEmployee = $data['listEmployee'] ?? [];
        $this->matrix = $data['matrix'] ?? [];
        $this->employeeContribution = $data['employeeContribution'] ?? [];
        $this->employerContribution = $data['employerContribution'] ?? [];
        $this->retenues = $data['retenues'] ?? [];
        $this->salariesDetails = $data['salariesDetails'] ?? [];
    }



    public function downloadPdf()
    {
        if (!$this->company || $this->isProcessing) {
            return;
        }

        $employeeChunks = array_chunk($this->listEmployee, $this->perPage, true);

        $pdf = Pdf::loadView('pdf.bulk-payroll-book', [
            'company' => $this->company,
            'payrollBook' => $this->payrollBook,
            'employeeChunks' => $employeeChunks,
            'matrix' => $this->matrix,
            'employeeContribution' => $this->employeeContribution,
            'employerContribution' => $this->employerContribution,
            'retenues' => $this->retenues,
            'salaries' => $this->salariesDetails,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Livre_de_paie_' . $this->company->name . '_' . now()->format('F_Y') . '.pdf');
    }

  
};
?>
<div>

    @if($this->payrollBook)
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Valider le livre de paie') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Vérifiez et validez votre livre de paie') }} </flux:text>
        </div>
    </div>
    <div class="relative flex h-screen rounded-xl">


        {{-- Right area for the PDF component --}}
        <div class="relative w-full overflow-y-auto rounded-xl bg-gray-50 p-4 dark:bg-zinc-950">

            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Aperçu du livre de paie
                </h3>
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" wire:click="downloadPdf" icon="arrow-down-tray" />
                    <flux:button wire:click="refreshPayrollBook" icon="arrow-path-rounded-square" />

                    
                </div>
            </div>
            {{-- Show Payroll Book Preview --}}
            <div class="overflow-x-auto rounded-xl bg-white p-4 shadow-sm dark:bg-zinc-900">
                @php
                $pagedList = array_slice($listEmployee, ($currentPage - 1) * $perPage, $perPage, true);
                @endphp
                @include('pdf.payroll-book-content', [
                'status' => $payrollBook->status,
                'company' => $company,
                'listEmployee' => $pagedList,
                'matrix' => $matrix,
                'employeeContribution' => $employeeContribution,
                'employerContribution' => $employerContribution,
                'retenues' => $retenues,
                'salaries' => $salariesDetails,
                'showPagination' => true,
                'currentPage' => $currentPage,
                'totalPages' => ceil(count($listEmployee) / $perPage),
                ])

            </div>
        </div>
    </div>

    @else
    <div class="flex flex-col items-center justify-center h-64 text-zinc-500">
        <svg class="w-16 h-16 mb-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-lg font-medium text-zinc-900 dark:text-white">Le livre de paie sera généré une fois les bulletins générés et validés.</p>
        <p class="text-sm">Pour générer et valider les bulletins cliquez  <a href="{{ route('pay.check.payslips') }}" class="text-blue-500 underline">ici</a>.</p>
    </div>

    @endif
</div>