<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;



#[Fillable(['employee_id', 'motif', 'department', 'job_title', 'contract_type', 'end_date', 'start_date',
'base_salary', 'smic', 'average_salary', 'added_by', 'category'])]
class ContractArchive extends Model
{

public function employee(){

    return $this->belongsTo(Employee::class);
} 



    protected static function booted()
    {
        static::creating(function ($contract) {
            if (empty($contract->added_by)):
                $contract->added_by = auth()->user()->name;
            endif;
        });
        static::updating(function ($contract) {
              if ($contract->added_by):
                $contract->added_by = auth()->user()->name;
            endif;
        });
    //
}
}
