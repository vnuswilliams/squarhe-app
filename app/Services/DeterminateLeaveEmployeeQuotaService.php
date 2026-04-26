<?php

namespace App\Services;

use App\Models\Employee;



class DeterminateLeaveEmployeeQuotaService
{


    public function handle(Employee $employee)
    {
       $dataLeaveCompanySetting =$employee->company->data['leaves'];
        $dataemployee =$employee->data;
        
        $dataemployee['leaves_majority'] = $dataLeaveCompanySetting['monthlyLeave'];

        $dataemployee['leaves_seniority'] = $dataLeaveCompanySetting['seniorLeave'];


        $dataemployee['leaves_child'] = $dataLeaveCompanySetting['childLeave'];
       $employee->data = $dataemployee;
       $employee->saveQuietly();
    }
}
