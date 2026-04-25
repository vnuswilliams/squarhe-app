<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 


class EmployeeContribution extends Model
{

    protected $fillable = [
        'ref',
        'employee_id',
        'old_age_pension',
        'irpp',
        'cac',
        'cfc',
        'syndicat',
        'rav',
        'tdl'

    ];

    protected $casts =  [
        'old_age_pension' => 'integer',
        'irpp' => 'integer',
        'cac' => 'integer',
        'cfc' => 'integer',
        'syndicat' => 'integer',
        'rav' => 'integer',
        'tdl' => 'integer'

    ];


    public function getTotalAttribute()
    {

        $total =
            $this->old_age_pension +
            $this->irpp +
            $this->cac +
            $this->cfc +
            $this->syndicat +
            $this->rav +
            $this->tdl;
        return number_format($total, 0, '', '');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    protected static function booted()
    {
        static::creating(function ($employeecon) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */


            if (empty($employeecon->ref)) {
                $employeecon->ref = (string) $ref;
            }
        });
    }
}
