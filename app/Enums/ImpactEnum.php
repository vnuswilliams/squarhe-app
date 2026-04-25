<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum ImpactEnum: string
{

    use EnumTrait;
    case TAXCOT = 'taxcot';
    case COTISABLE = 'cotisable';
    case TAXABLE = 'taxable';
    case NEUTRE = 'neutre';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::TAXCOT => 'Taxable & Côtisation',
            self::TAXABLE => 'Taxable',
            self::COTISABLE => 'Côtisable',
            self::NEUTRE => 'Neutre',
            self::AUTRE => 'Autre',
        };
    }
}
