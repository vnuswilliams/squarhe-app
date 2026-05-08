<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\RemunerationEnum;
use Illuminate\Database\Eloquent\Model;

class IranSnapshot extends Model
{
    use HasSnapshot;

    
    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
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
}
