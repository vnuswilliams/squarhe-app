<?php

namespace App\Livewire\Forms;

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\NationalityEnum;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AddEmployee extends Form
{
    public $civility;
    public $name;
    public $email;
    public $phone;
    public $birth_date;
    public $nationality;
    public $child = 0;
    public $niu;
    public $cnps_number;
    public $status;

    // Contract properties
    public $department;
    public $job_title;
    public $contract_type;
    public $start_date;
    public $end_date;
    public $base_salary;
    public $category;
    public function rules(): array
    {
        return [
            'civility' => ['required', Rule::in(CivilityEnum::values())],
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:30', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{9}$/', 'unique:employees,phone'],
            'birth_date' => ['nullable', 'date'],
            'nationality' => ['required', Rule::in(NationalityEnum::values())],
            'child' => ['required', 'integer', 'min:0'],
            'niu' => ['nullable', 'string', 'max:20', 'unique:employees,niu'],
            'cnps_number' => ['nullable', 'string', 'max:255', 'unique:employees,cnps'],

            'department' => ['required', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:50'],
            'contract_type' => ['required', Rule::in(ContractTypeEnum::values())],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'max:3', 'regex:/^(?:[1-9]|1[0-2])(?:[A-Ga-g])?$/'],
        ];
    }
    public function create()
    {

        Gate::authorize('create', Employee::class);
        $validatedData = $this->validate();
        $dataField = [
            'birth_date',
            'nationality',
            'civility',
            'phone',
            'child',
            'niu',
            'cnps_number',
            'email',
            'category'
        ];

        //extraction
        $data = collect($validatedData)
            ->only($dataField)
            ->toArray();

        //suppresion des champs
        $employeeData  = collect($validatedData)
            ->except($dataField)
            ->toArray();

        //injection
        $employeeData['data'] = $data;


        return auth()->user()->company->employees()->
        create($employeeData);


       
    }
}
