<?php

namespace App\Jobs;

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportEmployeeRemunerationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $path, public int $employeeId, public ?int $userId = null) {}

    public function handle(): void
    {
        $employee = Employee::findOrFail($this->employeeId);
        $rows = (new FastExcel())->import(Storage::path($this->path));

        foreach ($rows as $row) {
            $data = [
                'name' => $row['name'] ?? null,
                'amount' => $row['amount'] ?? null,
                'periodicity' => $row['periodicity'] ?? null,
                'impact' => $row['impact'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $validated = Validator::make($data, [
                'name' => ['required', Rule::in(RemunerationEnum::values())],
                'amount' => ['required', 'numeric', 'min:100'],
                'periodicity' => ['required', Rule::in(PeriodicityEnum::values())],
                'impact' => ['required', Rule::in(ImpactEnum::values())],
                'notes' => ['nullable', 'string', 'max:100'],
            ])->validate();

            $validated['type'] = RemunerationEnum::from($validated['name'])->type();
            $employee->remunerations()->create($validated);
        }

        Storage::delete($this->path);
    }
}
