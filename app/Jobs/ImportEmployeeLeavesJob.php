<?php

namespace App\Jobs;

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\User;
use App\Services\CalculateDays;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportEmployeeLeavesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $path, public int $employeeId, public ?int $userId = null) {}

    public function handle(): void
    {
        $employee = Employee::findOrFail($this->employeeId);
        $rows = (new FastExcel())->import(Storage::path($this->path));

        foreach ($rows as $row) {
            $data = [
                'type' => $row['type'] ?? null,
                'start_date' => $row['start_date'] ?? null,
                'end_date' => $row['end_date'] ?? null,
                'notes' => $row['notes'] ?? null,
                'last_leave' => $row['last_leave'] ?? null,
            ];

            $validated = Validator::make($data, [
                'type' => ['required', Rule::in(LeaveTypeEnum::values())],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'notes' => ['nullable', 'string', 'max:100'],
                'last_leave' => ['nullable', 'date'],
            ])->validate();

            $validated['days'] = app(CalculateDays::class)->calculateDays($validated['start_date'], $validated['end_date']);
            $validated['status'] = StatusEnum::APPROVED->value;
            $validated['approved_by'] = User::find($this->userId)?->name;

            $employee->leaves()->create($validated);
        }

        Storage::delete($this->path);
    }
}
