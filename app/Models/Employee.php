<?php

namespace App\Models;

use App\Concerns\EmployeeAccessors;
use App\Enums\StatusEnum;
use App\Models\Scopes\EmployeeScope;
use App\Observers\EmployeeObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;



#[ObservedBy(EmployeeObserver::class)]
#[ScopedBy(EmployeeScope::class)]
#[Fillable([
    'uuid',
    'company_id',
    'name',
    'status',
    'department',
    'job_title',
    'contract_type',
    'start_date',
    'end_date',
    'base_salary',
    'data'
])]
class Employee extends Model
{
    //*
    use EmployeeAccessors;

    protected function casts()
    {
        return [
            'end_date' => 'date',
            'start_date' => 'date',
            'base_salary' => 'integer',
            'data' => 'array'
        ];
    }
    public function getRouteKeyName()
    {
        return 'uuid';
    }
    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }
    public function salary()
    {
        return $this->hasOne(Salary::class);
    }
    public function employeeContributions()
    {
        return $this->hasOne(EmployeeContribution::class);
    }
    public function employerContributions()
    {
        return $this->hasOne(EmployerContribution::class);
    }
   
    public function advnats()
    {
        return $this->hasMany(AdvNat::class)->latest();
    }

     public function documents()
    {
        return $this->hasMany(Document::class)->latest();
    }

    public function irans()
    {
        return $this->hasMany(Iran::class)->latest();
    }
    public function remunerations()
    {
        return $this->hasMany(Remuneration::class)->latest();
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class)->latest();
    }

    public function leaves()

    {
        return $this->hasMany(Leave::class)->latest();
    }
    public function contractArchives()
    {
        return $this->hasMany(ContractArchive::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected static function booted()
    {
        static::creating(function ($employee) {
            if (empty($employee->uuid)):
                $employee->uuid = (string) Str::uuid();
            endif;
            if (empty($employee->status)):
                $employee->status = StatusEnum::APPROVED;
            endif;
        });
    }
}
