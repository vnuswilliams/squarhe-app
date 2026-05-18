<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Models\Employee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateStatusEmployeeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Employee $employee, public StatusEnum|string $status)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(!$this->employee) return;
        
            $this->employee->update([
'status' => $this->status,
            ]);
    }
}
