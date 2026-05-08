<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Services\CalculateSalaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateSalariesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Employee $employee) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(CalculateSalaryService::class)->handle($this->employee);
    }
}
