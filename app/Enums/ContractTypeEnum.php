<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum ContractTypeEnum: string
{
    use EnumTrait;


    case CDD = 'CDD';
    case CDI = 'CDI';
    case ESSAY = 'ESSAY';
    case INTERNSHIP = 'INTERNSHIP';


    public function label(): string
    {
        return match ($this) {
            self::CDD => 'Contrat à durée déterminée',
            self::CDI => 'Contrat à durée indéterminée',
            self::ESSAY => 'Période d\'essai',
            self::INTERNSHIP => 'Stage'
        };
    }
}
