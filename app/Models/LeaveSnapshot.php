<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\StatusEnum;
use App\Enums\LeaveTypeEnum;
use Illuminate\Database\Eloquent\Model;
  
class LeaveSnapshot extends Model
{

use HasSnapshot;

    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days',
        'status',
        'notes',
        'approved_by',
        'approbation_date',
        'last_leave',
        'leaves_balance',
        'leaves_majority',
        'leaves_seniority',
        'leaves_child'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'integer',
        'last_leave' => 'date',
        'leaves_balance' => 'integer',
        'leaves_majority' => 'integer',
        'leaves_seniority' => 'integer',
        'leaves_child' => 'integer',
        'status' => StatusEnum::class,
        'type' => LeaveTypeEnum::class,
    ];

  
}
