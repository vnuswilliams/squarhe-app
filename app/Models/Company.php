<?php

namespace App\Models;

use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use App\Observers\CompanyObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'phone', 'adresse', 'city', 'niu', 'cnps', 'rccm', 'company_code', 'data'])]
#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    use SoftDeletes;

    protected function casts()
    {
        return [
            'data' => 'array',
        ];
    }
    public function payrollBook()
    {
        return $this->hasOne(PayrollBook::class);
    }
    public function declarations()
    {
        return $this->hasOne(Declaration::class);
    }

    
    public function activeEmployees()
    {
        return $this->employees()
            ->where('status', '!=', StatusEnum::TERMINATED->value);
    }
    public function scopeIsNotInternship($query)
    {
        return $query->where('contract_type', '!=', ContractTypeEnum::INTERNSHIP->value);
    }

    
    public function scopeEmployeesWithoutPayslip()
    {
        return $this->activeEmployees()->isNotInternship()->where(function ($q) {
            $q->doesntHave('payslip')->orWhereHas('payslip', function ($sub) {
                $sub->where('status', StatusEnum::PENDING->value);
            });
        });
        
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    protected static function booted()
    {
        static::creating(function ($company) {
            if (empty($company->data)):
                $company->data = config('squarhe.defaults');
            endif;
            if (empty($company->company_code)) {
                $company->company_code = (string) Str::uuid();
            }
        });
    }
}
