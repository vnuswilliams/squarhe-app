<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use Illuminate\Database\Eloquent\Model;

class RemunerationSnapshot extends Model
{
    use HasSnapshot;

    
    protected $fillable = [
        'ref',
        'payroll_closure_id',
            'employee_snapshot_id',
        'employee_id',
        'name',
        'type',
        'amount',
        'periodicity',
        'impact',
        'added_by',
        'notes',
    ];

    protected function casts()
    {

        return [
            'amount' => 'integer',
            'name' => RemunerationEnum::class,
            'impact' => ImpactEnum::class,
            'type' => RemunerationTypeEnum::class,
            'periodicity' => PeriodicityEnum::class,
        ];
    }
    //
}
