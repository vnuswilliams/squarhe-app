<?php

namespace App\Concerns;

trait EnumTrait

{
    public static function options(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'label' => $case->label(),
            'value' => $case->value
        ])->toArray();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}