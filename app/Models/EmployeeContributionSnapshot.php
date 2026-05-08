<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use Illuminate\Database\Eloquent\Model;

class EmployeeContributionSnapshot extends Model
{
    use HasSnapshot;

    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
        'employee_id',
        'old_age_pension',
        'irpp',
        'cac',
        'cfc',
        'syndicat',
        'rav',
        'tdl'

    ];

    protected $casts =  [
        'old_age_pension' => 'integer',
        'irpp' => 'integer',
        'cac' => 'integer',
        'cfc' => 'integer',
        'syndicat' => 'integer',
        'rav' => 'integer',
        'tdl' => 'integer'

    ];


    public function getTotalAttribute()
    {

        $total =
            $this->old_age_pension +
            $this->irpp +
            $this->cac +
            $this->cfc +
            $this->syndicat +
            $this->rav +
            $this->tdl;
        return number_format($total, 0, '', '');
    }
   


}

