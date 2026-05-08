<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollClosure extends Model
{
    protected $fillable = [
        'company_id',
        'ref',
        'status',
        'closed_at',
        'closed_by',
        'send_payslips_by_email',
        'scheduled_at',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'closed_at' => 'date',
        'scheduled_at' => 'datetime',
        'send_payslips_by_email' => 'boolean',
    ];

    public function leaves()
    {
        return $this->hasMany(LeaveSnapshot::class);
    }

    public function payrollBook()
    {
        return $this->hasMany(PayrollBookSnapshot::class);

    }

    public function overtimes()
    {
        return $this->hasMany(OvertimeSnapshot::class);
    }

    public function salaries()
    {
        return $this->hasMany(SalarySnapshot::class);
    }

    public function employeeContributions()
    {
        return $this->hasMany(EmployeeContributionSnapshot::class);
    }

    public function employerContributions()
    {
        return $this->hasMany(EmployerContributionSnapshot::class);
    }

    public function payslip()
    {
        return $this->hasMany(PayslipSnapshot::class);
    }

    /**
     * Get the remunerations for the model.
     */
    public function remunerations(): HasMany
    {
        return $this->hasMany(RemunerationSnapshot::class);
    }

    /**
     * Get the irans for the model.
     */
    public function irans(): HasMany
    {
        return $this->hasMany(IranSnapshot::class);
    }

    /**
     * Get the advnats for the model.
     */
    public function advnats(): HasMany
    {
        return $this->hasMany(AdvNatSnapshot::class);
    }

    public function declarations()
    {
        return $this->hasMany(DeclarationSnapshot::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
