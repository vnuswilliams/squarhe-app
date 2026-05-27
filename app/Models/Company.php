<?php

namespace App\Models;

use App\Concerns\CompanyHasManyThrough;
use App\Enums\StatusEnum;
use App\Observers\CompanyObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Squarhe\Subscription\Models\Concerns\HasSubscriptions;

#[Fillable(['name', 'email', 'phone', 'adresse', 'city', 'niu', 'cnps', 'rccm', 'company_code', 'data'])]
#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    use SoftDeletes;
    use HasSubscriptions;
    use CompanyHasManyThrough;

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    // ─────────────────────────────────────────────
    //  Relations de base
    // ─────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class)->latest();
    }

    public function payrollClosures(): HasMany
    {
        return $this->hasMany(PayrollClosure::class);
    }

    public function payrollBook()
    {
        return $this->hasOne(PayrollBook::class);
    }

    public function declarations()
    {
        return $this->hasOne(Declaration::class);
    }

    // ─────────────────────────────────────────────
    //  Raccourcis de requêtes (retournent un Builder
    //  chainable sur les scopes d'Employee)
    // ─────────────────────────────────────────────

    /**
     * Point d'entrée : employés actifs (non résiliés).
     * Tous les raccourcis ci-dessous partent de cette base.
     *
     * @return HasMany|Builder<Employee>
     */
    public function activeEmployees(): HasMany|Builder
    {
        return $this->employees()->active();
    }

    /**
     * Employés actifs, hors stagiaires — idéal pour la paie.
     *
     * @return HasMany|Builder<Employee>
     */
    public function payrollEmployees(): HasMany|Builder
    {
        return $this->activeEmployees()->notInternship();
    }

    /**
     * Employés actifs qui n'ont pas encore de payslip
     * OU dont le payslip est toujours en statut PENDING.
     *
     * @return HasMany|Builder<Employee>
     */
    public function employeesNeedingPayslip(): HasMany|Builder
    {
        return $this->payrollEmployees()->needsPayslip();
    }

    /**
     * Filtre les employees actifs, ayant un payslip avec un statut particulier.
     *
     * @param  StatusEnum|string  $value
     * @return HasMany|Builder<Employee>
     */
    public function employeesWithPayslipStatus(StatusEnum|string $value): HasMany|Builder
    {
        return $this->payrollEmployees()->withPayslipStatus($value);
    }

    // ─────────────────────────────────────────────
    //  Booted
    // ─────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            $company->data         ??= config('squarhe.defaults');
            $company->company_code ??= (string) Str::uuid();
        });
    }
}