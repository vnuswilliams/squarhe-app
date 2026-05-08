<?php

namespace App\Models;

use App\Enums\RemunerationEnum;
use Illuminate\Database\Eloquent\Model;



class AdvNatSnapshot extends Model
{
    protected $fillable = [
        "ref",
        'employee_snapshot_id',
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


}
