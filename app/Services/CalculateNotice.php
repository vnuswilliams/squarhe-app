<?php

namespace App\Services;

use App\Enums\Impact;
use App\Enums\Periodicity;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
use App\Models\Employee;




class CalculateNotice
{
    /**
     * Caculate the amount of the notice
     * @param \App\Models\Employee $employee the employee we want to calculate the notice
     */
    public function __construct(public Employee $employee, public bool $inDatabase = false)
    {
    }
public function handle(){
    
        $age = $this->employee->contract->start_date->age;
        $cat = preg_match('/^(?:[1-9]|1[0-2])/', $this->employee->contract->professional_category, $match);
        $cat = $match[0];
        $noticeDay = 0;
        if ($age < 1):
            switch (true) {
                case ($cat <= 6):
                    $noticeDay = 0.5;
                    break;
                case ($cat >= 7 && $cat <= 9):
                    $noticeDay = 1;
                    break;
                case ($cat >= 10 && $cat >= 12):
                    $noticeDay = 1;
                    break;
            }
        endif;
        if ($age >= 1 && $age <= 5):
            switch (true) {
                case ($cat <= 6):
                    $noticeDay = 1;
                    break;
                case ($cat >= 7 && $cat <= 9):
                    $noticeDay = 2;
                    break;
                case ($cat >= 10 && $cat >= 12):
                    $noticeDay = 3;
                    break;
            }
        endif;
        if ($age > 5):
            switch (true) {
                case ($cat <= 6):
                    $noticeDay = 2;
                    break;
                case ($cat >= 7 && $cat <= 9):
                    $noticeDay = 3;
                    break;
                case ($cat >= 10 && $cat >= 12):
                    $noticeDay = 4;
                    break;
            }
        endif;

        $grossSalary = $this->employee->salaries->first()->gross_salary ?? 0;

        $noticeAmount = $grossSalary * $noticeDay;

        if ($this->inDatabase) {
            $this->employee->remunerations()->updateOrCreate(
                [
                    'name' => RemunerationElement::INDEMNITE_PREAVIS->value,
                    'company_id' => $this->employee->company->id
                ],
                [
                    'company_id' => $this->employee->company->id,
                    'name' => RemunerationElement::INDEMNITE_PREAVIS->value,
                    'type' => RemunerationType::ALLOCATION->value,
                    'amount' => number_format($noticeAmount, 0,'', ''),
                    'periodicity' => Periodicity::MONTHLY->value,
                    'impact' => Impact::TAXCOT->value,
                    'notes' => 'Indemintés de préavis de '.$this->employee->name.'(durée :.' . $noticeDay  .')'

                ]

            );
        } else {
            return number_format($noticeAmount, 0,'', '');
        }
        return 0;
    }
}