<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait EmployeeAccessors
{



public function getShortNameAttribute()
{
    return Str::limit($this->name, 10, '.');
}
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

    public function getAncAttribute()
    {
        $diff = $this->start_date->diff(now());

        if($diff->y === 0)
            {
                return $diff->m . ' mois';
            }
        if($diff->m === 0)
            {
                return $diff->y .' '.  ($diff->y > 1 ? 'ans' : 'an');
            }

            return $diff->y  .' '. ($diff->y > 1 ? 'ans' : 'an').' et '.  $diff->m . ' mois';
    }
}
