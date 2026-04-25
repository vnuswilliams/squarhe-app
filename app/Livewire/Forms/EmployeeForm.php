<?php

namespace App\Livewire\Forms;

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\NationalityEnum;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EmployeeForm extends Form
{
    public $employee;
    public bool $isCreating = true;


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
    public $average_salary;
    public $smic;
    public function rules(): array
    {
        return [
            'civility' => [$this->isCreating ? 'required' : 'nullable', Rule::in(CivilityEnum::values())],
            'name' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:50'],
            'email' => [$this->isCreating ? 'required' : 'nullable', 'string', 'email', 'max:30', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{9}$/', 'unique:employees,phone'],
            'birth_date' => ['nullable', 'date'],
            'nationality' => [$this->isCreating ? 'required' : 'nullable', Rule::in(NationalityEnum::values())],
            'child' => [$this->isCreating ? 'required' : 'nullable', 'integer', 'min:0'],
            'niu' => ['nullable', 'string', 'max:20', 'unique:employees,niu'],
            'cnps_number' => ['nullable', 'string', 'max:255', 'unique:employees,cnps'],

            'department' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:30'],
            'job_title' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:50'],
            'contract_type' => [$this->isCreating ? 'required' : 'nullable', Rule::in(ContractTypeEnum::values())],
            'start_date' => [$this->isCreating ? 'required' : 'nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'base_salary' => [$this->isCreating ? 'required' : 'nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'max:3', 'regex:/^(?:[1-9]|1[0-2])(?:[A-Ga-g])?$/'],
            'average_salary'  => ['nullable', 'numeric', 'min:0',],
            'smic' => ['nullable', 'numeric', 'min:0',]
        ];
    }
    public function setEmployee($employee)
    {
        $this->employee = $employee;



        $this->civility = $this->employee->data['civility'];
        $this->name = $this->employee->name;
        $this->email = $this->employee->data['email'];
        $this->phone = $this->employee->data['phone'];
        $this->birth_date = $this->employee->data['birth_date'];
        $this->nationality = $this->employee->data['nationality'];
        $this->child = $this->employee->data['child'];
        $this->niu = $this->employee->data['niu'];
        $this->cnps_number = $this->employee->data['cnps_number'];
    }

    public function setContract($employee)
    {
        $this->employee = $employee;


        $this->department = $employee->department;
        $this->job_title = $employee->job_title;
        $this->contract_type = $employee->contract_type;
        $this->start_date = $employee->start_date?->format('Y-m-d');
        $this->end_date = $employee->end_date?->format('Y-m-d');
        $this->base_salary = $employee->base_salary;
        $this->category = $this->employee->data['category'];
        $this->average_salary = $this->employee->data['average_salary'];
        $this->smic = $this->employee->data['smic'];
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
            'category',
            'average_salary',
            'smic'
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


        return auth()->user()->company->employees()->create($employeeData);
    }

    public function update()
    {
        Gate::authorize('update', [Employee::class, $this->employee]);
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
            'category',
            'average_salary',
            'smic'
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

        $employeeData  = array_filter($employeeData, fn($value) => !is_null($value));
        $this->employee->update($employeeData);
        //$this->reset();
    }
   
}
