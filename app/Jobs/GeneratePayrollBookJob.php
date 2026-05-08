<?php

namespace App\Jobs;

use App\Services\GeneratePayrollBookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePayrollBookJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int|string $company_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(GeneratePayrollBookService::class)->handle($this->company_id);
    }
}
