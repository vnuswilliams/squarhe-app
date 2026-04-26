<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Declaration extends Model
{
   protected $fillable = [
        'company_id',
        'ref',
        'data',
        'status'
    ];

    protected $casts = [
        'data' => 'array',
        'status' => StatusEnum::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    protected static function booted()
    {
        static::creating(function ($declaration) {


            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', StatusEnum::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');



            if (empty($declaration->ref)) {
                $declaration->ref = $ref;
            }
        });
    }
}
