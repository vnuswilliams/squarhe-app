<?php

namespace App\Livewire\Forms;

use App\Models\Company;
use Livewire\Attributes\Validate;
use Livewire\Form;


class SettingsCompanyForm extends Form
{
    public Company $company;

    #[Validate(['required', 'boolean'])]
    public bool $rav = false;

    #[Validate(['required', 'boolean'])]
    public bool $tdl = false;

    #[Validate(['required', 'boolean'])]
    public bool $irpp = false;

    #[Validate(['required', 'numeric', 'min:0'])]
    public float $labourHours;

    #[Validate(['required', 'string'])]
    public string $paymentMethod;

    #[Validate(['required', 'string'])]
    public string $applicable_law;

    #[Validate(['required', 'array'])]
    public array $seniorityBonus = [
        'enabled' => false,
        'rate' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $familyAllowances = [
        'enabled' => false,
        'rate' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $accident = [
        'enabled' => false,
        'rate' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $leaves = [
        'monthlyLeave' => 0.0,
        'seniorLeave' => 0.0,
        'childLeave' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $oldAgePension = [
        'enabled' => false,
        'employerShare' => 0.0,
        'employeeShare' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $cfc = [
        'enabled' => false,
        'employerShare' => 0.0,
        'employeeShare' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $cac = [
        'enabled' => false,
        'employeeShare' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $fne = [
        'enabled' => false,
        'employerShare' => 0.0
    ];

    #[Validate(['required', 'array'])]
    public array $fixedHolidays = [];

    public function setCompany(Company $company)
    {
        $this->company = $company;

        foreach ($this->all() as $key => $value) {
            if (isset($company->data[$key])) {
                $this->$key = $company->data[$key];
            }
        }
    }

    public function save()
    {
        $this->validate();

        $this->company->update([
            'data' => $this->all()
        ]);
    }
}
