<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Payslip extends Model
{
    protected $fillable = [
        'ref',
        'employee_id',
        'employee_data',
        'company_data',
        'status',
        'elements_data',
        'employee_contribution',
        'employer_contribution',
        'retenues_data',
        'salaries_data',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'employee_data' => 'array',
        'company_data' => 'array',
        'elements_data' => 'array',
        'employee_contribution' => 'array',
        'employer_contribution' => 'array',
        'retenues_data' => 'array',
        'salaries_data' => 'array',
    ];
    public function getFormattedContributionsAttribute()
    {
        $contributionsArray = [];
        if ($this->employee_contribution) {
            foreach ($this->employee_contribution as $row) {
                if (isset($row['amount']) && $row['amount'] != 0) {
                    $contributionsArray[$row['code']] = [
                        'code' => $row['code'],
                        'label' => $row['label'],
                        'employee' => $row['amount'],
                        'employer' => 0,
                    ];
                }
            }
        }
        if ($this->employer_contribution) {
            foreach ($this->employer_contribution as $row) {
                if (isset($row['amount']) && $row['amount'] != 0) {
                    if (!isset($contributionsArray[$row['code']])) {
                        $contributionsArray[$row['code']] = [
                            'code' => $row['code'],
                            'label' => $row['label'],
                            'employer' => $row['amount'],
                            'employee' => 0,
                        ];
                    } else {
                        $contributionsArray[$row['code']]['employer'] = $row['amount'];
                    }
                }
            }
        }

        usort($contributionsArray, function ($a, $b) {
            return intval($a['code']) <=> intval($b['code']);
        });

        return $contributionsArray;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getFormattedSalariesAttribute()
    {
        if (!$this->salaries_data) return [];

        return collect($this->salaries_data)
            ->mapWithKeys(function ($item) {
                $key = array_key_first($item);
                return [$key => $item[$key]];
            })
            ->toArray();
    }

    protected static function booted()
    {
        static::creating(function ($payslip) {

            $ref = now()->format('m-Y');
            /*            auth()->user()?->companies()->with('payrollClosures')->first()
                ->payrollClosures()->where('ref', now()->format('m-Y'))
                ->where('status', PayrollClosureStatus::LOCKED)->first() ? $ref = now()->addMonth()->format('m-Y')  :  $ref = now()->format('m-Y');
*/

            if (empty($payslip->ref)) {
                $payslip->ref = $ref;
            }
            if (empty($payslip->status)) {
                $payslip->status = StatusEnum::PENDING->value;
            }
        });
    }
}
