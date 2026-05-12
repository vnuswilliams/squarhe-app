<?php

namespace App\Concerns;

use App\Models\AdvNatSnapshot;
use App\Models\EmployeeContributionSnapshot;
use App\Models\EmployerContributionSnapshot;
use App\Models\IranSnapshot;
use App\Models\LeaveSnapshot;
use App\Models\OvertimeSnapshot;
use App\Models\PayrollBookSnapshot;
use App\Models\PayslipSnapshot;
use App\Models\RemunerationSnapshot;
use App\Models\SalarySnapshot;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSnaps
{
    public function leavesSnapshot()
    {
        return $this->hasMany(LeaveSnapshot::class)->latest();
    }

  
    public function overtimesSnapshot()
    {
        return $this->hasMany(OvertimeSnapshot::class)->latest();
    }

    public function salariesSnapshot()
    {
        return $this->hasMany(SalarySnapshot::class)->latest();
    }

    public function employeeContributionsSnapshot()
    {
        return $this->hasMany(EmployeeContributionSnapshot::class)->latest();
    }

    public function employerContributionsSnapshot()
    {
        return $this->hasMany(EmployerContributionSnapshot::class)->latest();
    }

    public function payslipSnapshot()
    {
        return $this->hasMany(PayslipSnapshot::class)->latest();
    }

    /**
     * Get the remunerations for the model.
     */
    public function remunerationsSnapshot(): HasMany
    {
        return $this->hasMany(RemunerationSnapshot::class)->latest();
    }

    /**
     * Get the irans for the model.
     */
    public function iransSnapshot(): HasMany
    {
        return $this->hasMany(IranSnapshot::class)->latest();
    }

    /**
     * Get the advnats for the model.
     */
    public function advnatsSnapshot(): HasMany
    {
        return $this->hasMany(AdvNatSnapshot::class)->latest();
    }

  
}
