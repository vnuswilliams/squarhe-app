<?php

namespace App\Jobs;

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Services\CalculateDays;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportEmployeeLeavesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $path, public string|int $employeeId, public ?string $userName = null) {}

    public function handle(): void
    {
        $rows = (new FastExcel)->import(Storage::path($this->path));
        try {

            DB::transaction(function () use ($rows) {

                $employee = Employee::whereId($this->employeeId);
                if ($employee) {
                    foreach ($rows as $row) {
                        $data = [
                            'type' => $row['type'] ?? null,
                            'start_date' => $row['start_date'] ?? null,
                            'end_date' => $row['end_date'] ?? null,
                            'notes' => $row['notes'] ?? null,
                            'last_leave' => $row['last_leave'] ?? null,
                            'approved_by' => $row['approved_by'] ?? null,
                            'added_by' => $row['added_by'] ?? null,
                        ];

                        $validated = Validator::make($data, [
                            'type' => ['required', Rule::in(LeaveTypeEnum::values())],
                            'start_date' => ['required', 'date'],
                            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                            'notes' => ['nullable', 'string', 'max:100'],
                            'last_leave' => ['nullable', 'date'],
                            'approved_by' => ['required', 'string'],
                            'added_by' => ['nullable', 'string', 'max:20'],
                        ])->validate();

                        $validated['days'] = app(CalculateDays::class)->calculateDays($validated['start_date'], $validated['end_date']);
                        $validated['status'] = StatusEnum::APPROVED->value;

                        $employee->leaves()->create($validated);
                    }
                }
            });
        } finally {

            Storage::delete($this->path);
        }

    }
}
