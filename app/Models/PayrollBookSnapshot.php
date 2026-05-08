<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayrollBookSnapshot extends Model
{

use HasSnapshot;

   
    protected $fillable = [
        'ref',
        'payroll_closure_id',
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

}
