<?php

namespace App\Livewire\Forms;

use App\Models\Company;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UpdateCompanyForm extends Form
{
    public Company $company;

    #[Validate('sometimes|string|max:60')]
    public $name = '';

    #[Validate('sometimes|string|max:50')]
    public $email = '';

    #[Validate('sometimes|digits:9')]
    public $phone = '';

    #[Validate('sometimes|string|max:30')]
    public $city = '';

    #[Validate('sometimes|string|max:30')]
    public $adresse = '';

    #[Validate('sometimes|string|max:30')]
    public $nui = '';

    #[Validate('sometimes|string|max:30')]
    public $cnps = '';

    #[Validate('sometimes|string|max:30')]
    public $rccm = '';

    public function setCompany(Company $company): void
    {
        $this->company = $company;
        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->city = $company->city;
        $this->adresse = $company->adresse;
        $this->nui = $company->nui;
        $this->cnps = $company->cnps;
        $this->rccm = $company->rccm;
    }

    public function update(): void
    {
        $validatedData = $this->validate();

        $this->company->update($validatedData);
    }
}
