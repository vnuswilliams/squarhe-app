<?php

namespace App\Concerns;

trait EmployeeAccessors
{
    //
    public function getCivilityAttribute()
    {
        return $this->data['civility'] ?? null; 
    }
    public function getPhoneAttribute()
    {
        return $this->data['phone'] ?? null; 
    }
    public function getNationalityAttribute()
    {
        return $this->data['nationality'] ?? null; 
    }

    public function getChildAttribute()
    {
        return $this->data['child'] ?? null; 
    }
    public function getBdayAttribute()
    {
        return $this->data['birth_date'] ?? null; 
    }
    public function getNiuAttribute()
    {
        return $this->data['niu'] ?? null; 
    }
    public function getCnpsAttribute()
    {
        return $this->data['cnps_number'] ?? null; 
    }
    public function getEmailAttribute()
    {
        return $this->data['email'] ?? null; 
    }
    public function getCategoryAttribute()
    {
        return $this->data['category'] ?? null; 
    }
}
