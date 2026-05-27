<?php

namespace App\Concerns;

use App\Models\AdvNat;
use App\Models\ContractArchive;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeContribution;
use App\Models\EmployerContribution;
use App\Models\EndContract;
use App\Models\Iran;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payslip;
use App\Models\Remuneration;
use App\Models\Salary;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

trait CompanyHasManyThrough
{
    public function payslips(): HasManyThrough
    {
        return $this->hasManyThrough(Payslip::class, Employee::class);
    }

    public function salaries(): HasManyThrough
    {
        return $this->hasManyThrough(Salary::class, Employee::class);
    }

    public function remunerations(): HasManyThrough
    {
        return $this->hasManyThrough(Remuneration::class, Employee::class);
    }

    public function overtimes(): HasManyThrough
    {
        return $this->hasManyThrough(Overtime::class, Employee::class);
    }

    public function leaves(): HasManyThrough
    {
        return $this->hasManyThrough(Leave::class, Employee::class);
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, Employee::class);
    }

    public function irans(): HasManyThrough
    {
        return $this->hasManyThrough(Iran::class, Employee::class);
    }

    public function advNats(): HasManyThrough
    {
        return $this->hasManyThrough(AdvNat::class, Employee::class);
    }

    public function employeeContributions(): HasManyThrough
    {
        return $this->hasManyThrough(EmployeeContribution::class, Employee::class);
    }

    public function employerContributions(): HasManyThrough
    {
        return $this->hasManyThrough(EmployerContribution::class, Employee::class);
    }

    public function contractArchives(): HasManyThrough
    {
        return $this->hasManyThrough(ContractArchive::class, Employee::class);
    }

    public function endContracts(): HasManyThrough
    {
        return $this->hasManyThrough(EndContract::class, Employee::class);
    }
}
