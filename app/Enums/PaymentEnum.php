<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum PaymentEnum: string
{
    use EnumTrait;

    case CASH = 'cash';
    case CHECK = 'check';
    case BANK_TRANSFERT = 'bank_transfert';
    case MOBILE_PAYMENT = 'mobile_payment';


    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash', self::BANK_TRANSFERT => 'Virement bancaire',
            self::CHECK => 'Chèque',
            self::MOBILE_PAYMENT => 'Paiement mobile'
        };
    }
}
