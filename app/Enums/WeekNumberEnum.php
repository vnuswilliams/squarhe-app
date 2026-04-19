<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum WeekNumberEnum: string
{
    use EnumTrait;

    case FIRST = '1';
    case SECOND = '2';
    case THIRD = '3';
    case FOURTH = '4';
    case FIFTH = '5';

    public function label(): string
    {
        return match ($this) {
            self::FIRST => __('week_number_enum.firstweek'),
            self::SECOND => 'Second Week',
            self::THIRD => 'Third Week',
            self::FOURTH => 'Fourth Week',
            self::FIFTH => 'Fifth Week',
        };
    }


}
