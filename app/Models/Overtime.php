<?php

namespace App\Models;

use App\Enums\HsuppEnum;
use Illuminate\Database\Eloquent\Model;



class Overtime extends Model
{

    protected $fillable = [
        'ref',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function booted()
    {
        static::creating(function ($overtime) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */

            if (empty($overtime->ref)) {
                $overtime->ref = (string) $ref;
            }

            if (empty($overtime->added_by)):
                $overtime->added_by = auth()->user()->name;
            endif;
            if (empty($overtime->alloc)):
                $overtime->alloc = number_format($overtime->hours * $overtime->hours_rate * $overtime->multiplier, 0, '','');
            endif;
        });
        static::updating(function ($overtime) {
            if ($overtime->added_by):
                $overtime->added_by = auth()->user()->name;
            endif;
            if ($overtime->alloc):
                $overtime->alloc = number_format($overtime->hours * $overtime->hours_rate * $overtime->multiplier, 0, '','');
            endif;
        });
    }
}
