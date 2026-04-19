<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum NationalityEnum: string{
    use EnumTrait;

    case CAMEROONIAN = 'camerounais';
    case FOREIGN = 'etrangere';
    

      public function label(): string
    {
        return match ($this) {
            self::CAMEROONIAN => 'Camerounaise',
            self::FOREIGN => 'Étrangère',
        };
    }
}
