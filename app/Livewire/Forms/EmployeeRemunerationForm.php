<?php

namespace App\Livewire\Forms;

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Models\Employee;
use App\Models\Remuneration;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EmployeeRemunerationForm extends Form
{
    public $employee_id, $name, $type, $amount, $periodicity, $impact, $notes;
    public $remun;
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'name' => ['required', Rule::in(RemunerationEnum::values())],
            'type' => ['required', Rule::in(RemunerationTypeEnum::values())],
            'amount' => ['required', 'numeric', 'min:100'],
            'periodicity' => ['required', Rule::in(PeriodicityEnum::values())],
            'impact' => ['required', Rule::in(ImpactEnum::values())],
            'notes' => ['nullable', 'string', 'max:100'],
        ];
    }
    public function create()
    {
        Gate::authorize('create', Remuneration::class);
        $remunerationToCreate = $this->validate();
        $employee = Employee::find($this->employee_id);
        $employee->remunerations()->create($remunerationToCreate);
    }

    public function setRemun($remun)
    {
        $this->remun = $remun;
        $this->employee_id = $remun->employee_id;
        $this->name = $remun->name?->value;
        $this->type = $remun->type?->value;
        $this->amount = $remun->amount;
        $this->periodicity = $remun->periodicity?->value;
        $this->impact = $remun->impact?->value;
        $this->notes = $remun->notes;
    }

    public function update()
    {
        Gate::authorize('update', [Remuneration::class, $this->remun]);
        $validatedata = $this->validate();
        $this->remun->update($validatedata);
    }
}
