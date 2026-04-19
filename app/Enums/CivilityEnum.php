<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum CivilityEnum: string
{
    use EnumTrait;
    //
    case MALE = 'homme';
    case FEMALE = 'femme';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Homme',
            self::FEMALE => 'Femme',
        };
    }
}
