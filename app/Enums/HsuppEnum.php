<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum HsuppEnum: string
{
    use EnumTrait;
    case HEURE_SUPP_120 = 'heure_supp_120';
    case HEURE_SUPP_130 = 'heure_supp_130';
    case HEURE_SUPP_140 = 'heure_supp_140';
    case HEURE_SUPP_150 = 'heure_supp_150';
    case HEURE_SUPP_200 = 'heure_supp_200';


public function dayType(): float
{
     return match ($this) {
            self::HEURE_SUPP_120 => 1.2,
            self::HEURE_SUPP_130 => 1.3,
            self::HEURE_SUPP_140 => 1.4,
            self::HEURE_SUPP_150 => 1.5,
            self::HEURE_SUPP_200 => 2,

        };
}

    public function label(): string
    {
        return match ($this) {
            self::HEURE_SUPP_120 => 'Heures supplémentaires (120%)',
            self::HEURE_SUPP_130 => 'Heures supplémentaires (130%)',
            self::HEURE_SUPP_140 => 'Heures supplémentaires (140%)',
            self::HEURE_SUPP_150 => 'Heures supplémentaires (150%)',
            self::HEURE_SUPP_200 => 'Heures supplémentaires (200%)',

        };
    }
}
