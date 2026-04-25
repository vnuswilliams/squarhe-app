<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
     protected $fillable = [
        'ref',
        'employee_id',
        'base_salary',
        'gross_salary',
        'intermediate_taxable_gross_salary',
        'taxable_gross_salary',
        'contributory_salary',
        'average_salary',
        'smic',
        'retenues',
        'contributions',
        'nap'
    ];

     protected $casts = [
        'base_salary' => 'integer',
        'gross_salary' => 'integer',
        'intermediate_taxable_gross_salary' => 'integer',
        'taxable_gross_salary' => 'integer',
        'contributory_salary' => 'integer',
        'average_salary' => 'integer',
        'smic' => 'integer',
        'retenues' => 'integer',
        'contributions' => 'integer',
        'nap' => 'integer'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

     protected static function booted()
    {
        static::creating(function ($salary) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */

            if (empty($salary->ref)) {
                $salary->ref = $ref;
            }
        });
    }
}
