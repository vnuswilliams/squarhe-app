<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayrollBook extends Model
{

   
    protected $fillable = [
        'ref',
        'company_id',
        'uuid',
        'status',
        'data',
    ];


    protected $casts = [
        'status' => StatusEnum::class,
        'data' =>  'array',
        'uuid' => 'string'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function booted()
    {
        static::creating(function ($payrollBook) {


            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', StatusEnum::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');



            if (empty($payrollBook->ref)) {
                $payrollBook->ref = (string) $ref;
            }
            if (empty($payrollBook->uuid)) {
                $payrollBook->uuid = (string) Str::uuid();
            }
        });
    }
}
