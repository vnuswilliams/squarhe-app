<?php

namespace App\Models;

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Observers\RemunerationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;


#[ObservedBy(RemunerationObserver::class)]
class Remuneration extends Model
{

    protected $fillable = [
        'ref',
        'employee_id',
        'name',
        'type',
        'amount',
        'periodicity',
        'impact',
        'added_by',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    #[Scope]
    public function sumByName(Builder $query)
    {
        //         'name, SUM(amount) as total_amount'
        return $query
            ->selectRaw('name, SUM(amount) as total_amount')
            ->groupBy('name');
    }
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

        protected static function booted()
    {

        static::creating(function ($remuneration) {

            $ref = now()->format('m-Y');
            /*auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
            */

                $remuneration->ref ??= (string) $ref;
                $remuneration->added_by ??= auth()->user()->name;
        });

        static::updating(function ($remuneration) {
                $remuneration->added_by ??= auth()->user()->name;
        });
    }
}
