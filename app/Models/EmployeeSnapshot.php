<?php

namespace App\Models;

use App\Concerns\EmployeeAccessors;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'payroll_closure_id',
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
class EmployeeSnapshot extends Model
{
    use EmployeeAccessors;

    protected function casts(): array
    {
        return [
            'end_date'    => 'date',
            'start_date'  => 'date',
            'base_salary' => 'integer',
            'data'        => 'array',
            'status'      => StatusEnum::class,
        ];
    }


  

    // ─────────────────────────────────────────────
    //  Relations
    // ─────────────────────────────────────────────

    public function payslip()
    {
        return $this->hasOne(PayslipSnapshot::class);
    }

    public function salary()
    {
        return $this->hasOne(SalarySnapshot::class);
    }

    public function employeeContributions()
    {
        return $this->hasOne(EmployeeContributionSnapshot::class);
    }

    public function employerContributions()
    {
        return $this->hasOne(EmployerContributionSnapshot::class);
    }

    public function advnats()
    {
        return $this->hasMany(AdvNatSnapshot::class)->latest();
    }

    public function irans()
    {
        return $this->hasMany(IranSnapshot::class)->latest();
    }

    public function remunerations()
    {
        return $this->hasMany(RemunerationSnapshot::class)->latest();
    }

    public function overtimes()
    {
        return $this->hasMany(OvertimeSnapshot::class)->latest();
    }

    public function leaves()
    {
        return $this->hasMany(LeaveSnapshot::class)->latest();
    }

    public function payrollClosure()
    {
        return $this->belongsTo(PayrollClosure::class);
    }
}