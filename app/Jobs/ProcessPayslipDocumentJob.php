<?php

namespace App\Jobs;

use App\Enums\DocumentAccessEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessPayslipDocumentJob
{
    use Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Employee $employee,
        public StatusEnum $status
    ) {}

    public function handle()
    {
        $currentMonth = now()->format('F_Y');

        $fileName = Str::snake($this->employee->shortName.' '.$currentMonth.'.pdf');
        $path = $this->employee->company_id.'/'.$this->employee->id.'/payslips/'.'/'.$fileName;

        $documentName = 'Bulletin de paie de '.now()->format('F Y');

        /*
        |--------------------------------------------------------------------------
        | GENERATION DU PDF
        |--------------------------------------------------------------------------
        */

        if ($this->status === StatusEnum::APPROVED) {

            $pdf = Pdf::loadView('pdf.payslip', [
                'employee' => $this->employee,
            ]);

            $saveDoc = $this->employee->documents()->updateOrCreate(
                [
                    'employee_id' => $this->employee->id,
                    'type' => DocumentTypeEnum::BULLETIN_PAIE->value,
                    'name' => $documentName,
                ],
                [
                    'type' => DocumentTypeEnum::BULLETIN_PAIE->value,
                    'name' => $documentName,
                    'notes' => 'Bulletin de paie généré  pour '.$currentMonth,
                    'path' => $path,
                    'access' => DocumentAccessEnum::ADMIN->value,
                ]
            );

            $saveDoc ?: Storage::disk('public')->put($path, $pdf->output());

        }

        /*
        |--------------------------------------------------------------------------
        | SUPPRESSION DU DOCUMENT
        |--------------------------------------------------------------------------
        */

        if ($this->status === StatusEnum::PENDING) {

            $document = $this->employee->documents
                ->where('type', DocumentTypeEnum::BULLETIN_PAIE->value)
                ->where('name', $documentName)
                ->first();

            if ($document) {
                Storage::disk('public')->exists($document->path) ?: Storage::disk('public')->delete($document->path);

                $document->delete();
            }
        }
    }
}
