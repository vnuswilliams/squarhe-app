<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'motiff', 'department', 'job_title', 'contractt_type', 'end_date', 'start_date',
'base_salary', 'category'])]
class ContractArchive extends Model
{

public function employee(){

    return $this->belongsTo(Employee::class);
} 
    //
}
