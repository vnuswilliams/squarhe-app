<?php

namespace App\Models;

use App\Concerns\EmployeeAccessors;
use App\Enums\StatusEnum;
use App\Observers\EmployeeObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[ObservedBy(EmployeeObserver::class)]

#[Fillable(['uuid', 'company_id', 'name', 'status', 'department', 'job_title', 'contract_type',
'start_date', 'end_date', 'base_salary', 'data'])]
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
    public function leaves() 

    {
        return $this->hasMany(Leave::class);
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
