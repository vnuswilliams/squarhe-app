<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Services\GenerateDeclarationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDeclarationJob implements ShouldQueue
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

      
        app(GenerateDeclarationService::class)->handle($this->company_id);
    }
}
