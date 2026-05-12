<?php

namespace App\Jobs;

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Models\Employee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportEmployeeRemunerationsJob implements ShouldQueue
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
                            'name' => $row['name'] ?? null,
                            'amount' => $row['amount'] ?? null,
                            'periodicity' => $row['periodicity'] ?? null,
                            'impact' => $row['impact'] ?? null,
                            'notes' => $row['notes'] ?? null,
                            'added_by' => $row['added_by'] ?? null,
                        ];

                        $validated = Validator::make($data, [
                            'name' => ['required', Rule::in(RemunerationEnum::values())],
                            'amount' => ['required', 'numeric', 'min:100'],
                            'periodicity' => ['required', Rule::in(PeriodicityEnum::values())],
                            'impact' => ['required', Rule::in(ImpactEnum::values())],
                            'notes' => ['nullable', 'string', 'max:100'],
                            'added_by' => ['nullable', 'string', 'max:20']
                        ])->validate();

                        $validated['type'] = RemunerationEnum::from($validated['name'])->type();
                        $employee->remunerations()->create($validated);
                    }
                }
            });
        } finally {

            Storage::delete($this->path);
        }
    }
}
