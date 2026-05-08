<?php

namespace App\Concerns;

use App\Models\Employee;
use App\Models\EmployeeSnapshot;
use App\Models\PayrollClosure;

trait HasSnapshot
{
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeSnapshot()
    {
        return $this->belongsTo(EmployeeSnapshot::class);
    }

    public function payrollClosure()
    {
        return $this->belongsTo(PayrollClosure::class);
    }
}
