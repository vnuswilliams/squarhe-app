<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class EmployerContribution extends Model
{


    protected $fillable = [
        'ref',
        'employee_id',
        'family_allowance',
        'old_age_pension',
        'accident',
        'cfc',
        'fne'
    ];

    protected $casts = [
        'family_allowance' => 'integer',
        'old_age_pension' => 'integer',
        'accident' => 'integer',
        'cfc' => 'integer',
        'fne' => 'integer',
    ];

    public function getTotalAttribute()
    {

        $total = $this->old_age_pension +
            $this->family_allowance +
            $this->accident +
            $this->cfc +
            $this->fne;
        return number_format($total, 0, '', '');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    protected static function booted()
    {
        static::creating(function ($employrcon) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */


            if (empty($employrcon->ref)) {
                $employrcon->ref = (string) $ref;
            }
        });
    }
}
