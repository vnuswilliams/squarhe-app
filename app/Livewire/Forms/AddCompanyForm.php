<?php

namespace App\Livewire\Forms;

use App\Models\Company;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AddCompanyForm extends Form
{

    #[Validate('required|string|max:60|unique:companies,name')]
    public $name = '';

    #[Validate('required|string|max:50|unique:companies,email')]
    public $email = '';

    #[Validate('required|digits:9|unique:companies,phone')]
    public $phone = '';

    #[validate('required|string|max:30')]
    public $city = '';

    #[Validate('required|string|max:30')]
    public $adresse = '';
    #[Validate('nullable|string|max:30|unique:companies,niu')]
    public $niu = '';

    #[Validate('nullable|string|max:30|unique:companies,cnps')]
    public $rccm = '';

    #[Validate('nullable|string|max:30|unique:companies,rccm')]
    public $cnps = '';

    public function  store()
    {
        $validatedData = $this->validate();
        Company::create($validatedData);

        $this->reset();
    }
}
