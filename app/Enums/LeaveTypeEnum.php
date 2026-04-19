<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum LeaveTypeEnum: string
{

    use EnumTrait;
    case ANNUAL = 'congé_annuel';                // Droit annuel (1 à 2.5 jours/mois)
    case SICK = 'congé_maladie';                 // Avec certificat médical
    case SUSPENSION = 'mise_a_pied';
    case INJUSTIFY_LEAVE = 'congé_injustifié';          // Sans justification
    case JUSTIFY_LEAVE = 'congé_justifié';          // Sans justification
    case MATERNITY = 'congé_maternité';          // 14 semaines (Cameroun)
    case PATERNITY = 'congé_paternité';          // 3 à 10 jours
    case UNPAID = 'congé_sans_solde';            // Non rémunéré
    case SPECIAL = 'congé_exceptionnel';         // Décision de la direction


    /**
     * Retourne le libellé lisible pour l’utilisateur
     */
    public function label(): string
    {
        return match ($this) {
            self::ANNUAL => 'Congé annuel',
            self::SICK => 'Congé maladie',
            self::MATERNITY => 'Congé maternité',
            self::PATERNITY => 'Congé paternité',
            self::UNPAID => 'Congé sans solde',
            self::SPECIAL => 'Congé exceptionnel',
            self::INJUSTIFY_LEAVE => 'Absences injustifiées',
            self::JUSTIFY_LEAVE =>'Absences justifiées',         // Sans justification
            self::SUSPENSION => 'Mise à pied',
        };
    }



  
}
