<?php

namespace App\Jobs;

use App\Enums\HsuppEnum;
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

class ImportEmployeeOvertimesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $path, public int $employeeId, public ?int $userId = null) {}

    public function handle(): void
    {
        $employee = Employee::findOrFail($this->employeeId);
        $rows = (new FastExcel())->import(Storage::path($this->path));

        foreach ($rows as $row) {
            $data = [
                'day_type' => $row['day_type'] ?? null,
                'hours' => $row['hours'] ?? null,
                'hours_rate' => $row['hours_rate'] ?? null,
                'week' => $row['week'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $validated = Validator::make($data, [
                'day_type' => ['required', Rule::in(HsuppEnum::values())],
                'hours' => ['required', 'numeric', 'min:1'],
                'hours_rate' => ['required', 'numeric', 'min:1'],
                'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
                'notes' => ['nullable', 'string', 'max:100'],
            ])->validate();

            $validated['multiplier'] = HsuppEnum::from($validated['day_type'])->dayType();

            $employee->overtimes()->create($validated);
        }

        Storage::delete($this->path);
    }
}
