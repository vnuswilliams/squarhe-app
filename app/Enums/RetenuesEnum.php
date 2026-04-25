<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum RetenuesEnum: string
{
    use  EnumTrait;
    case RETENUE_AVANCE_SALAIRE = 'retenue_avance_salaire';
    case RETENUE_PRET_EMPLOYE = 'retenue_pret_employe';
    case RETENUE_SANCTION = 'retenue_sanction_disciplinaire';
    case SAISIE_SALAIRE = 'saisie_salaire';
    case RETENUE_CANTINE = 'retenue_cantine';
    case ACCOMPTE_SALAIRE = 'accompte_salaire';
    case RETENUE_ABSENCES = 'retenues_absences';


    public function label(): string
    {
        return match ($this) {
            self::RETENUE_AVANCE_SALAIRE => 'Retenue avance sur salaire',
            self::RETENUE_PRET_EMPLOYE => 'Retenue prêt employé',
            self::RETENUE_SANCTION => 'Retenue sanction disciplinaire',
            self::SAISIE_SALAIRE => 'Saisie sur salaire',
            self::RETENUE_CANTINE => 'Retenue cantine',
            self::ACCOMPTE_SALAIRE => 'Accompte sur salaire',
            self::RETENUE_ABSENCES => 'Retenues absences',
        };
    }
    public function code(): string
    {
        return match ($this) {


            self::RETENUE_AVANCE_SALAIRE => '700',
            self::RETENUE_PRET_EMPLOYE => '701',
            self::RETENUE_SANCTION => '702',
            self::SAISIE_SALAIRE => '703',
            self::RETENUE_CANTINE => '704',
            self::ACCOMPTE_SALAIRE => '705',
            self::RETENUE_ABSENCES => '706',
        };
    }
}
