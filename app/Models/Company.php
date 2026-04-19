<?php

namespace App\Models;

use App\Observers\CompanyObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['name',        'email',        'phone',        'adresse',        'city',        'nui',        'cnps',        'rccm',        'join_code',        'data',])]
#[ObservedBy(CompanyObserver::class)]
class Company extends Model
{
    protected function casts()
    {
        return [
            'data' => 'array'
        ];
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
            if (empty($company->join_code)) {
                $company->join_code = (string) Str::uuid();
            }
        });
    }
}
