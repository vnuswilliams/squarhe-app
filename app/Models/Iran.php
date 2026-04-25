<?php

namespace App\Models;

use App\Enums\RemunerationEnum;
use Illuminate\Database\Eloquent\Model;



class Iran extends Model
{


    protected $fillable = [
        'ref',
        'employee_id',
        'name',
        'amount',
        'limit_fisc',
        'quote'
    ];
    protected $casts = [
        'name' => RemunerationEnum::class,
        'quote' => 'integer',
        'amount' => 'integer',
        'limit_fisc' => 'integer',
    ];

  public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted()
    {
        static::creating(function ($iran) {

            $ref = now()->format('m-Y');
            /*
            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */

            if (empty($iran->ref)) {
                $iran->ref = $ref;
            }
            if (empty($iran->quote)):
                $iran->quote = min($iran->amount, $iran->limit_fisc);
            endif;
        });
    }
}
