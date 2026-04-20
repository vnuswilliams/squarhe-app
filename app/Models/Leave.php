<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Enums\LeaveTypeEnum;
use App\Observers\LeaveObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(LeaveObserver::class)]
class Leave extends Model
{
    protected $fillable = [
        'ref',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted()
    {
        static::creating(function ($leave) {


            /*  auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');

*/
            if (empty($leave->ref)) {
                $leave->ref = now()->format('m-Y');
            }
            if(empty($leave->approbation_date)):
                $leave->approbation_date = now();
            endif;

            if (empty($leave->approved_by)):
                $leave->approved_by = auth()->user()->name;
            endif;
        });
    }
}
