<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Services\CalculateImpotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class calculateImpotJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Employee $employee)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(CalculateImpotService::class)->handle($this->employee);
    }
}
