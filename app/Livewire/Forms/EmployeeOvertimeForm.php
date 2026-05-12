<?php

namespace App\Livewire\Forms;

use App\Enums\HsuppEnum;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EmployeeOvertimeForm extends Form
{
    public $employee_id, $notes, $day_type, $hours, $hours_rate, $multiplier, $alloc, $week;
    public $overtime;
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'notes' => ['nullable', 'string', 'max:100'],
            'day_type' => ['required', Rule::in(HsuppEnum::values())],
            'hours' => ['required', 'numeric', 'min:1'],
            'hours_rate' => ['required', 'numeric', 'min:1'],
            'multiplier' => ['required', 'numeric', Rule::in([1.2, 1.3, 1.4, 1.5, 2])],
            'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
        ];
    }
    public function create()
    {
        Gate::authorize('create', Overtime::class);
        $overtimeToCreate = $this->validate();
        $employee = Employee::whereId($this->employee_id);
        $employee->overtimes()->create($overtimeToCreate);
    }

    public function setOvertime($overtime)
    {
        $this->overtime = $overtime;
        $this->employee_id = $overtime->employee_id;

        $this->week = $overtime->week;
        $this->notes = $overtime->notes;
        $this->day_type = $overtime->day_type?->value;
        $this->hours = $overtime->hours;
        $this->hours_rate = $overtime->hours_rate;
        $this->alloc = $overtime->alloc;
    }

    public function update()
    {
        Gate::authorize('update', [Overtime::class, $this->overtime]);
        $validatedata = $this->validate();
        $this->overtime->update($validatedata);
    }
}
