<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum MotifEnum : string
{
    use EnumTrait;

    case ILLNESS = 'illness';
    case ILLNESS_WORK_ACCIDENT = 'work_accident';
    case MATERNITY = 'maternity'; //ok
    case TECHNICAL_UNEMPLOYMENT = 'technical_unemployment'; //ok
    case DISCIPLINARY = 'disciplinary'; //ok
    case CONSERVATOIRE = "conservatoire"; //ok
    case DISMISSAL = 'dismissal'; //ok
    case RESIGNATION = 'resignation'; //ok
    case MUTUAL_AGREEMENT = 'mutual_agreement';


    public function label(): string
    {
        return match ($this) {
            self::ILLNESS => 'Maladie non professionnel',
            self::ILLNESS_WORK_ACCIDENT => 'Maladie professionnel et accident de travail',
            self::MATERNITY => 'Maternité',
            self::TECHNICAL_UNEMPLOYMENT => 'Chômage technique',
            self::DISCIPLINARY => 'Mise à pied disciplinaire',
            self::CONSERVATOIRE => 'Mise à pied conservatoire',
            self::DISMISSAL => 'Licenciement conventionnelle',
            self::RESIGNATION => 'Démission',
            self::MUTUAL_AGREEMENT => "Départ en retraite",
        };
    }



    public function description(): string
    {
        return match ($this) {
            self::ILLNESS => "Elle ne peut exceder 6mois à partir de la date à laquelle l'employé fait parvenir un certificat médical dans un bref délai, ce dernier contient la date probable de reprise du travail mais cette estimation reste provisoire. Apres 6mois d'attente vous êtes libre de remplacer le salarié si non reprise. NB: un licenciement sans remplacement peut être requalifier en licenciiement abusif.
            Durant cette période, le travailleur recoit une somme d'argent qui est égale à l'indemnité de préavis si la durée dabsence est égale ou supérieure à celle du préavis auqeul le travailleur pourrait prétendre s'il était licencié à ce moment-là. Si la période est plus courte que la durée du préavis, l'indemnité est égale à la rémunération qu'aurait perçue le travailleur s'il avait travaillé pendant cette période. Ce monde est le même pour un CDD ou CDI.",
            self::ILLNESS_WORK_ACCIDENT => "En cas de maladie  pro ou d'accident du travail la suspension dure toute la période d'indisponibilité le travailleur est pris en charge par la CNPS, cependant vous devez accomplir les formaliités nécéssaire pour cette prise en charge (voir CNPS) .",
            self::MATERNITY => "Le congé de maternité dure 14 semaines et commence 4semaines avant la date de l'accouchement prévu par le médécin.",
            self::TECHNICAL_UNEMPLOYMENT => "Chômage technique est une intérruption colective du travail, résultant soit d'une cause accidentelle ou de la force majeur, soit d'une conjoncture économique défavorable ne peut excéder 6mois, au délà l'employé doit se considérer comme licencié, sa base de calcul est le salaire de base majoré de la prime d'ancienneté.",
            self::DISCIPLINARY => "Mise à pied disciplinaire ne peut dépasser 8jours et doit être notifier à l'inspection du travail 48heures après décision.",
            self::CONSERVATOIRE => "Mise à pied conservatoire est prononcé à l'encontre des délégué du personnel et peut dure aussi longtemps qu'est attendue la décision de l'inspection du travail téritorialement compétent, si vous n'avez pas la date de fin et que vous ne metez rien par defaut la date de fin sera le dernier jour du mois en cours, vous pourrez proroger si nécessaire.",
            self::DISMISSAL => "Licenciement conventionnelle il donne droit au paiement d'une indemnité de licenciement même en cas de faute légére, elle n'est pas dû en cas d'engagement à l'essai, faute lourde ou démission.",
            self::RESIGNATION => 'Démission',
            self::MUTUAL_AGREEMENT => "d'un commun accord",
        };
    }
}
