<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\HsuppEnum;
use Illuminate\Database\Eloquent\Model;



class OvertimeSnapshot extends Model
{
use HasSnapshot;
    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
        'employee_id',
        'notes',
        'day_type',
        'hours',
        'hours_rate',
        'multiplier',
        'alloc',
        'week',
        'added_by'
    ];

    protected $casts = [
        'day_type' => HsuppEnum::class, // Assuming day_type is stored as a string enum
        'week' => 'integer',
        'hours' => 'float',
        'hours_rate' => 'float',
        'multiplier' => 'float',
        'alloc' => 'float',

    ];

   
}
