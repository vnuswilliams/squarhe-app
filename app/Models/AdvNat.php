<?php

namespace App\Models;

use App\Enums\RemunerationEnum;
use Illuminate\Database\Eloquent\Model;



class AdvNat extends Model
{
    protected $fillable = [
        "ref",
        'employee_id',
        'name',
        'amount',
        'limit_fisc',
        'excedent'
    ];
    protected $casts = [
        'name' => RemunerationEnum::class,
        'excedent' => 'integer',
        'limit_fisc' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted()
    {
        static::creating(function ($advnat) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */

            if (empty($advnat->ref)) {
                $advnat->ref = $ref;
            }
            if(empty($advnat->excedent)):
                    $advnat->excedent = max($advnat->amount, $advnat->limit_fisc) - min($advnat->amount, $advnat->limit_fisc);
            endif;
        });
    }
}
