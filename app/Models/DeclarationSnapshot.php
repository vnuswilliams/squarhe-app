<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class DeclarationSnapshot extends Model
{
    use HasSnapshot;
   protected $fillable = [
    'payroll_closure_id',
        'ref',
        'data',
        'status'
    ];

    protected $casts = [
        'data' => 'array',
        'status' => StatusEnum::class,
    ];
  
}
