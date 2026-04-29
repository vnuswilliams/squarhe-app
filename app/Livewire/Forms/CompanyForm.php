<?php

namespace App\Livewire\Forms;

use App\Models\Company;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CompanyForm extends Form
{
    public bool $isCreating = true;

    public $company;
    public ?string $name;
    public ?string $email;
    public ?int $phone;
    public ?string $city;
    public ?string $adresse;
    public ?string $niu;
    public ?string $cnps;
    public ?string $rccm;

    public function rules(): array
    {
        return [
            'name' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:60', Rule::unique('companies', 'name')->ignore($this->company?->id)],
            'email' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:50', Rule::unique('companies', 'email')->ignore($this->company?->id)],
            'phone' => [$this->isCreating ? 'required' : 'nullable', 'digits:9', Rule::unique('companies', 'phone')->ignore($this->company?->id)],
            'adresse' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:30'],
            'city' => [$this->isCreating ? 'required' : 'nullable', 'string', 'max:30'],
            'niu' => ['nullable', 'string', 'max:30', Rule::unique('companies', 'niu')->ignore($this->company?->id)],
            'cnps' => ['nullable', 'string', 'max:30', Rule::unique('companies', 'cnps')->ignore($this->company?->id)],
            'rccm' => ['nullable', 'string', 'max:30', Rule::unique('companies', 'rccm')->ignore($this->company?->id)],
        ];
    }

    public function setCompany(?Company $company)
    {
        if (!$company) {
            return;
        }

        $this->company = $company;

        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->city = $company->city;
        $this->adresse = $company->adresse;
        $this->niu = $company->niu;
        $this->cnps = $company->cnps;
        $this->rccm = $company->rccm;
    }

    public function create()
    {
        $validatedData = $this->validate();
        return auth()->user()->company()->create($validatedData);
    }

    public function update()
    {
        Gate::authorize('update', [Company::class, $this->company] );
        $validatedData = $this->validate();

        return $this->company->update($validatedData);
    }

    public function regenCompanyCode()
    {
        Gate::authorize('update', [Company::class, $this->company] );
         return $this->company->update([
            'company_code' => Str::uuid(),
        ]);
    }
}
