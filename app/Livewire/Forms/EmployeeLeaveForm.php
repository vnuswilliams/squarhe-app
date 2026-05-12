<?php

namespace App\Livewire\Forms;

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EmployeeLeaveForm extends Form
{
    public $employee_id;
    public  $type;
    public string $approved_by;
    public  $start_date, $end_date, $days, $status, $notes, $last_leave, $approbation_date;
    public $leave;
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'type' => ['required', Rule::in(LeaveTypeEnum::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'numeric'],
            'approved_by' => ['required', 'string'],
            'status' => ['required', Rule::in(StatusEnum::values())],
            'notes' => ['nullable', 'string', 'max:100'],
            'last_leave' => ['nullable', 'date']

        ];
    }

    public function setLeave($leave)
    {
        $this->leave = $leave;
        $this->employee_id = $leave->employee_id;
        $this->type = $leave->type?->value;
        $this->status = $leave->status?->value;
        $this->start_date = $leave->start_date?->format('Y-m-d');
        $this->end_date = $leave->end_date?->format('Y-m-d');
        $this->days = $leave->days;
        $this->approved_by = $leave->approved_by;
        $this->notes = $leave->notes;
        $this->approbation_date = now();

        if ($leave->type === LeaveTypeEnum::ANNUAL->value):
            $this->last_leave = $leave->last_leave;
        endif;
    }
    public function create()
    {
        Gate::authorize('create', Leave::class);

        $validateData = $this->validate();
        $employee = Employee::whereId($this->employee_id);
        $employee->leaves()->create($validateData);
    }
    public function update()
    {
        Gate::authorize('update', [Leave::class, $this->leave]);
        $validateData = $this->validate();
        $this->leave->update($validateData);
    }
}
