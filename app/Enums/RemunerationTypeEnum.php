<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum RemunerationTypeEnum: string
{

    use EnumTrait;
    case IMPOT = 'impot';
    case RETENU = 'retenu';
    case ALLOCATION = 'allocation';
    case PRIME = 'prime';
    case ADVANTAGE = 'advantage';
    case INDEMNITE = 'indemnite';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::ADVANTAGE => 'Avantage',
            self::INDEMNITE => 'Indemnité',
            self::PRIME => 'Prime',
            self::IMPOT => 'Impôt',
            self::RETENU => 'Retenu',
            self::ALLOCATION => 'Allocation',
            self::AUTRE => 'Autre',
        };
    }
}
