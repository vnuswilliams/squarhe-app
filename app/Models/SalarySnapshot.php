<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use Illuminate\Database\Eloquent\Model;

class SalarySnapshot extends Model
{
    use HasSnapshot;
    
    protected $fillable = [
        'payroll_closure_id',
        'ref',
        'employee_snapshot_id',
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
        'nap',
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
        'nap' => 'integer',
    ];

   
   
}
