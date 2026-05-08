<?php

namespace App\Models;

use App\Concerns\HasSnapshot;
use Illuminate\Database\Eloquent\Model;

class EmployerContributionSnapshot extends Model
{
    use HasSnapshot;


    

    protected $fillable = [
        'ref',
        'payroll_closure_id',
        'employee_snapshot_id',
        'employee_id',
        'family_allowance',
        'old_age_pension',
        'accident',
        'cfc',
        'fne'
    ];

    protected $casts = [
        'family_allowance' => 'integer',
        'old_age_pension' => 'integer',
        'accident' => 'integer',
        'cfc' => 'integer',
        'fne' => 'integer',
    ];

    public function getTotalAttribute()
    {

        $total = $this->old_age_pension +
            $this->family_allowance +
            $this->accident +
            $this->cfc +
            $this->fne;
        return number_format($total, 0, '', '');
    }
    
}
