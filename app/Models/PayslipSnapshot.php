<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;


class PayslipSnapshot extends Model
{
    use HasSnapshot;
    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
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

  
}
