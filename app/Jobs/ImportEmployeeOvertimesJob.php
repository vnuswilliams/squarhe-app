<?php

namespace App\Jobs;

use App\Enums\HsuppEnum;
use App\Models\Employee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportEmployeeOvertimesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $path, public string|int $employeeId) {}

    public function handle(): void
    {

        $rows = (new FastExcel)->import(Storage::path($this->path));
        try {

            DB::transaction(function () use ($rows) {
                $employee = Employee::whereId($this->employeeId);
                if ($employee) {
                    foreach ($rows as $row) {
                        $data = [
                            'day_type' => $row['day_type'] ?? null,
                            'hours' => $row['hours'] ?? null,
                            'hours_rate' => $row['hours_rate'] ?? null,
                            'week' => $row['week'] ?? null,
                            'notes' => $row['notes'] ?? null,
                            'added_by' => $row['added_by'] ?? null,
                        ];

                        $validated = Validator::make($data, [
                            'day_type' => ['required', Rule::in(HsuppEnum::values())],
                            'hours' => ['required', 'numeric', 'min:1'],
                            'hours_rate' => ['required', 'numeric', 'min:1'],
                            'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
                            'notes' => ['nullable', 'string', 'max:100'],
                            'added_by' => ['nullable', 'string', 'max:20']
                            ])->validate();

                        $validated['multiplier'] = HsuppEnum::from($validated['day_type'])->dayType();

                        $employee->overtimes()->create($validated);
                    }
                }
            });
        } finally {
            Storage::delete($this->path);
        }

    }
}
