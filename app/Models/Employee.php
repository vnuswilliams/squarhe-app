<?php

namespace App\Models;

use App\Concerns\EmployeeAccessors;
use App\Concerns\HasSnaps;
use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Scopes\EmployeeGlobalScope;
use App\Observers\EmployeeObserver;
use App\Policies\EmployeePolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[ObservedBy(EmployeeObserver::class)]
#[UsePolicy(EmployeePolicy::class)]
#[ScopedBy(EmployeeGlobalScope::class)]
#[Fillable([
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
    use EmployeeAccessors, HasUuids, HasSnaps;

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
    //  Scopes — statut
    // ─────────────────────────────────────────────

    /** Filtre par statut arbitraire. */
    public function scopeOfStatus(Builder $query, StatusEnum|string $status): Builder
    {
        $value = $status instanceof StatusEnum ? $status->value : $status;

        return $query->where('status', $value);
    }

    /** Employés dont le statut est APPROVED (validé). */
    public function scopeValidated(Builder $query): Builder
    {
        return $query->ofStatus(StatusEnum::APPROVED);
    }

    /** Employés dont le statut est PENDING. */
    public function scopePending(Builder $query): Builder
    {
        return $query->ofStatus(StatusEnum::PENDING);
    }

    /** Employés non résiliés (tout statut sauf TERMINATED). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', StatusEnum::TERMINATED->value);
    }

    // ─────────────────────────────────────────────
    //  Scopes — type de contrat
    // ─────────────────────────────────────────────

    /** Filtre par type de contrat arbitraire. */
    public function scopeOfContractType(Builder $query, ContractTypeEnum|string $type): Builder
    {
        $value = $type instanceof ContractTypeEnum ? $type->value : $type;

        return $query->where('contract_type', $value);
    }

    /** Exclut les stagiaires. */
    public function scopeNotInternship(Builder $query): Builder
    {
        return $query->where('contract_type', '!=', ContractTypeEnum::INTERNSHIP->value);
    }

    // ─────────────────────────────────────────────
    //  Scopes — payslip
    // ─────────────────────────────────────────────

    /** Employés qui n'ont aucun payslip. */
    public function scopeWithoutPayslip(Builder $query): Builder
    {
        return $query->doesntHave('payslip');
    }

    /** Employés qui ont un payslip (quel que soit son statut). */
    public function scopeWithPayslip(Builder $query): Builder
    {
        return $query->has('payslip');
    }

    /** Employés dont le payslip a un statut précis. */
    public function scopeWithPayslipStatus(Builder $query, StatusEnum|string $status): Builder
    {
        $value = $status instanceof StatusEnum ? $status->value : $status;

        return $query->whereHas('payslip', fn (Builder $q) => $q->where('status', $value));
    }

    /** Employés dont le payslip est en attente (PENDING). */
    public function scopeWithPendingPayslip(Builder $query): Builder
    {
        return $query->withPayslipStatus(StatusEnum::PENDING);
    }

    /**
     * Employés sans payslip OU dont le payslip est encore en attente.
     * (Reprend la logique de l'ancienne scopeEmployeesWithoutPayslip de Company.)
     */
    public function scopeNeedsPayslip(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->doesntHave('payslip')
              ->orWhereHas('payslip', fn (Builder $sub) => $sub->where('status', StatusEnum::PENDING->value));
        });
    }

    // ─────────────────────────────────────────────
    //  Relations
    // ─────────────────────────────────────────────

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

    // ─────────────────────────────────────────────
    //  Booted
    // ─────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $employee): void {
            $employee->status ??= StatusEnum::APPROVED->value;
        });
    }
}