<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum PeriodicityEnum: String
{
    use EnumTrait;
      case UNIQUE = 'unique';
    case MONTHLY = 'monthly';

 public function label(): string
    {
        return match ($this) {
            self::UNIQUE => 'Unique',
            self::MONTHLY => 'Mensuelle',
        };
    }
}
