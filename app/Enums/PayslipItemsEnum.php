<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum PayslipItemsEnum: string
{
    use EnumTrait;


        // --- 0xx : SALAIRES & ALLOCATIONS ---
    case SALAIRE_BASE = 'salaire_base';
    case SUR_SALAIRE = 'sursalaire';
    case TREIZIEME_MOIS = '13eme_mois';
    case ALLOCATION_CONGE = 'allocation_conge';
    case HEURE_SUPP = 'heure_supp';

        // --- 1xx : NAD ---
    case RETENUE_AVANCE_SALAIRE = 'retenue_avance_salaire';
    case RETENUE_PRET_EMPLOYE = 'retenue_pret_employe';
    case RETENUE_SANCTION = 'retenue_sanction_disciplinaire';
    case SAISIE_SALAIRE = 'saisie_salaire';
    case RETENUE_CANTINE = 'retenue_cantine';
    case ACCOMPTE_SALAIRE = 'accompte_salaire';
    case RETENUE_ABSENCES = 'retenues_absences';

        // --- 2xx : PRIMES ---
    case PRIME_ANCIENNETE = 'prime_anciennete';
    case PRIME_RENDEMENT = 'prime_rendement';
    case PRIME_PRODUCTION = 'prime_production';
    case PRIME_FONCTION = 'prime_fonction';
    case PRIME_RISQUE = 'prime_risque';
    case PRIME_CAISSE = 'prime_caisse';
    case PRIME_PANIER = 'prime_panier';
    case PRIME_ASSIDUITE = 'prime_assiduite';
    case PRIME_INSALUBRITE = 'PRIME_insalubrite';
    case PRIME_TECHNICITE = 'prime_technicite';
    case PRIME_OUTILLAGE = 'prime_outillage';

        // --- 3xx : INDEMNITES ---
    case INDEMNITE_LOGEMENT = 'indemnite_logement';
    case INDEMNITE_TRANSPORT = 'indemnite_transport';
    case INDEMNITE_SUJETION = 'indemnite_sujetion';
    case INDEMNITE_REPAS = 'indemnite_repas';
    case INDEMNITE_INSALUBRITE = 'indemnite_insalubrite';
    case INDEMNITE_SALISSURE = 'indemnite_salissure';
    case INDEMNITE_DEPLACEMENT = 'indemnite_deplacement';
    case INDEMNITE_LAIT = 'indemnite_lait';
    case INDEMNITE_REPRESENTATION = 'indemnite_representation';
    case INDEMNITE_BICYCLETTE = 'indemnite_bicyclette';
    case INDEMNITE_SECURITE = 'indemnite_securite';
    case INDEMNITE_USAGE_VEHICULE = 'indemnite_usage_vehicule';
    case INDEMNITE_LICENCIEMENT = 'indemnite_licenciement';
    case INDEMNITE_PREAVIS = 'indemnite_preavis';
    case INDEMNITE_CHOMAGE_TECHNIQUE = 'indemnite_chomage_technique';

        // --- 4xx : CHARGES SALARIALES ---
    case CNPS_VIEILLESSE_SALARIALE = 'cnps_pension_vieillesse_salariale';
    case CREDIT_FONCIER_SALARIALE = 'credit_foncier_salariale';
    case CENTIME_COMMUNAL = 'centime_additionnel_communal';
    case IRPP = 'irpp';
    case TAXE_DEVELOPPEMENT = 'taxe_developpement_locale';
    case REDEVANCE_AUDIO_VISUELLE = 'redevance_audio_visuelle';
    case SYNDICAT = 'syndicat';

        // --- 5xx : CAHRGE PATRONALE ---
    case CNPS_ALLOCATION_FAMILIALE = 'cnps_allocation_familiale';
    case CNPS_VIEILLESSE_PATRONALE = 'cnps_pension_vieillesse_patronale';
    case CREDIT_FONCIER_PATRONALE = 'credit_foncier_patronale';
    case CNPS_ACCIDENT_MALADIE_PRO = 'cnps_accident_maladie_pro';
    case FNE = 'fond_national_emploi';

        // --- 6xx : AVANTAGES EN NATURE ---
    case AVANTAGE_LOGEMENT = 'avantage_logement';
    case AVANTAGE_VEHICULE = 'avantage_vehicule';
    case AVANTAGE_NOURRITURE = 'avantage_nourriture';
    case AVANTAGE_DOMESTIQUE = 'avantage_domestique';
    case AVANTAGE_ELECTRICITE = 'avantage_electricite';
    case AVANTAGE_EAU = 'avantage_eau';
    case AVANTAGE_CARBURANT = 'avantage_carburant';
    case AVANTAGE_TELEPHONE = 'avantage_telephone';
    case AVANTAGE_INTERNET = 'avantage_internet';
    case AVANTAGE_GARDINNAGE = 'avantage_gardinnage';



        //indemnite representative des avant en nat
    case AVANTAGE_REP_LOGEMENT = 'avantage_rep_logement';
    case AVANTAGE_REP_VEHICULE = 'avantage_rep_vehicule';
    case AVANTAGE_REP_NOURRITURE = 'avantage_rep_nourriture';
    case AVANTAGE_REP_DOMESTIQUE = 'avantage_rep_domestique';
    case AVANTAGE_REP_ELECTRICITE = 'avantage_rep_electricite';
    case AVANTAGE_REP_EAU = 'avantage_rep_eau';
    case AVANTAGE_REP_CARBURANT = 'avantage_rep_carburant';
    case AVANTAGE_REP_TELEPHONE = 'avantage_rep_telephone';
    case AVANTAGE_REP_INTERNET = 'avantage_rep_internet';
    case AVANTAGE_REP_GARDINNAGE = 'avantage_rep_gardinnage';


    case  GROSS_SALARY = 'gross_salary';
    case  INTERMEDIATE_GROSS_SALARY = 'intermediate_gross_salary';
    case  TAXABLE_GROSS_SALARY = 'taxable_gross_salary';
    case  CONTRIBUTORY_SALARY = 'contributory_salary';
    case  AVERAGE_SALARY = 'average_salary';
    case  SMIC = 'smic';
    case  NAD = 'nad';
    case  NAP = 'nap';


    public function label(): string
    {
        return match ($this) {
            self::SALAIRE_BASE => 'Salaire de base',
            self::SUR_SALAIRE => 'Sursalaire',
            self::TREIZIEME_MOIS => '13ème mois',
            self::ALLOCATION_CONGE => 'Allocation de congé',
            self::HEURE_SUPP => 'Heures supp.',

            self::RETENUE_AVANCE_SALAIRE => 'Retenue avance sur salaire',
            self::RETENUE_PRET_EMPLOYE => 'Retenue prêt employé',
            self::RETENUE_SANCTION => 'Retenue sanction disciplinaire',
            self::SAISIE_SALAIRE => 'Saisie sur salaire',
            self::RETENUE_CANTINE => 'Retenue cantine',
            self::ACCOMPTE_SALAIRE => 'Accompte sur salaire',
            self::RETENUE_ABSENCES => 'Retenues absences',

            self::PRIME_ANCIENNETE => 'Prime d’ancienneté',
            self::PRIME_RENDEMENT => 'Prime de rendement',
            self::PRIME_PRODUCTION => 'Prime de production',
            self::PRIME_FONCTION => 'Prime de fonction',
            self::PRIME_RISQUE => 'Prime de risque',
            self::PRIME_CAISSE => 'Prime de caisse',
            self::PRIME_PANIER => 'Prime de panier',
            self::PRIME_ASSIDUITE => 'Prime d’assiduité',
            self::PRIME_INSALUBRITE => 'Prime d\'insalubrité',
            self::PRIME_TECHNICITE => 'Prime de technicité',

            self::INDEMNITE_LOGEMENT => 'Indem. de logement',
            self::INDEMNITE_TRANSPORT => 'Indem. de transport',
            self::INDEMNITE_SUJETION => 'Indem. de sujétion',
            self::INDEMNITE_REPAS => 'Indem. de repas',
            self::INDEMNITE_INSALUBRITE => 'Indem. d’insalubrité',
            self::INDEMNITE_SALISSURE => 'Indem. de salissure',
            self::INDEMNITE_DEPLACEMENT => 'Indem. de déplacement',
            self::INDEMNITE_LAIT => 'Indem. de lait',
            self::INDEMNITE_REPRESENTATION => 'Indem. de représentation',
            self::INDEMNITE_BICYCLETTE => 'Indem. de bicyclette',
            self::INDEMNITE_SECURITE => 'Indem. de sécurité',
            self::INDEMNITE_USAGE_VEHICULE => 'Indem. d’usage de véhicule',
            self::INDEMNITE_LICENCIEMENT => 'Indem. de licenciement',
            self::INDEMNITE_PREAVIS => 'Indem. de préavis',
            self::INDEMNITE_CHOMAGE_TECHNIQUE => 'Indem. de chômage technique',

            self::IRPP => 'IRPP (Impôt sur le revenu)',
            self::CENTIME_COMMUNAL => 'Centime additionnel communal',
            self::FNE => 'Fonds national de l’emploi (FNE)',
            self::CREDIT_FONCIER_SALARIALE => 'Crédit foncier du Cameroun',
            self::TAXE_DEVELOPPEMENT => 'Taxe de développement local',
            self::REDEVANCE_AUDIO_VISUELLE => 'Redevance audiovisuelle',
            self::SYNDICAT => 'Syndicat',

            self::CREDIT_FONCIER_PATRONALE => 'Crédit foncier du Cameroun',
            self::CNPS_VIEILLESSE_SALARIALE => 'CNPS – Pension vieillesse ',
            self::CNPS_VIEILLESSE_PATRONALE => 'CNPS – Pension vieillesse ',
            self::CNPS_ALLOCATION_FAMILIALE => 'CNPS – Allocation familiale',
            self::CNPS_ACCIDENT_MALADIE_PRO => 'CNPS – Accident et maladie pro',

            self::AVANTAGE_LOGEMENT => 'Advnat – logement',
            self::AVANTAGE_VEHICULE => 'Advnat – véhicule',
            self::AVANTAGE_NOURRITURE => 'Advnat – nourriture',
            self::AVANTAGE_DOMESTIQUE => 'Advnat – domestique',
            self::AVANTAGE_ELECTRICITE => 'Advnat – électricité',
            self::AVANTAGE_EAU => 'Advnat – eau',
            self::AVANTAGE_CARBURANT => 'Advnat - carburant',
            self::AVANTAGE_TELEPHONE => "Advnat - téléphone",
            self::AVANTAGE_INTERNET => 'Advnat - internet',
            self::AVANTAGE_GARDINNAGE => 'Advnat - gardinnage',


            self::AVANTAGE_REP_LOGEMENT => 'Iran – logement',
            self::AVANTAGE_REP_VEHICULE => 'Iran – véhicule',
            self::AVANTAGE_REP_NOURRITURE => 'Iran – nourriture',
            self::AVANTAGE_REP_DOMESTIQUE => 'Iran – domestique',
            self::AVANTAGE_REP_ELECTRICITE => 'Iran – électricité',
            self::AVANTAGE_REP_EAU => 'Iran – eau',
            self::AVANTAGE_REP_CARBURANT => 'Iran - carburant',
            self::AVANTAGE_REP_TELEPHONE => "Iran - téléphone",
            self::AVANTAGE_REP_INTERNET => 'Iran - internet',
            self::AVANTAGE_REP_GARDINNAGE => 'Iran - gardinnage',


            self::GROSS_SALARY => 'Salaire brut',
            self::INTERMEDIATE_GROSS_SALARY => 'Salaire brut taxable intermédiaire',
            self::TAXABLE_GROSS_SALARY => 'Salaire brut taxable',
            self::CONTRIBUTORY_SALARY => 'Salaire côtisable',
            self::AVERAGE_SALARY => 'Salaire moyen',
            self::SMIC => 'SMIC',
            self::NAD => 'Net à déduire',
            self::NAP => 'Net à payer',
        };
    }

    public function code(): string
    {
        return match ($this) {

            self::SALAIRE_BASE => '001',
            self::SUR_SALAIRE => '002',
            self::TREIZIEME_MOIS => '003',
            self::ALLOCATION_CONGE => '004',
            self::HEURE_SUPP => '005',

            self::PRIME_ANCIENNETE => '100',
            self::PRIME_RENDEMENT => '101',
            self::PRIME_PRODUCTION => '102',
            self::PRIME_FONCTION => '103',
            self::PRIME_RISQUE => '104',
            self::PRIME_CAISSE => '105',
            self::PRIME_PANIER => '106',
            self::PRIME_ASSIDUITE => '107',
            self::PRIME_INSALUBRITE => '108',
            self::PRIME_OUTILLAGE => '109',


            self::INDEMNITE_PREAVIS => '200',
            self::INDEMNITE_LICENCIEMENT => '201',
            self::INDEMNITE_CHOMAGE_TECHNIQUE => '202',
            self::INDEMNITE_REPAS => '203',
            self::INDEMNITE_INSALUBRITE => '204',
            self::INDEMNITE_SALISSURE => '205',
            self::INDEMNITE_SUJETION => '206',
            self::INDEMNITE_DEPLACEMENT => '207',
            self::INDEMNITE_LAIT => '208',
            self::INDEMNITE_REPRESENTATION => '209',
            self::INDEMNITE_BICYCLETTE => '210',
            self::INDEMNITE_SECURITE => '211',
            self::INDEMNITE_USAGE_VEHICULE => '212',
            self::INDEMNITE_TRANSPORT => '213',
            self::INDEMNITE_LOGEMENT => '214',

            self::AVANTAGE_LOGEMENT => '300',
            self::AVANTAGE_VEHICULE => '301',
            self::AVANTAGE_NOURRITURE => '302',
            self::AVANTAGE_DOMESTIQUE => '303',
            self::AVANTAGE_ELECTRICITE => '304',
            self::AVANTAGE_EAU => '305',
            self::AVANTAGE_CARBURANT => '306',
            self::AVANTAGE_TELEPHONE => "307",
            self::AVANTAGE_INTERNET => '308',
            self::AVANTAGE_GARDINNAGE => '309',


            //indemnite representative des avant en nat
            self::AVANTAGE_REP_LOGEMENT => '400',
            self::AVANTAGE_REP_VEHICULE => '401',
            self::AVANTAGE_REP_NOURRITURE => '402',
            self::AVANTAGE_REP_DOMESTIQUE => '403',
            self::AVANTAGE_REP_ELECTRICITE => '404',
            self::AVANTAGE_REP_EAU => '405',
            self::AVANTAGE_REP_CARBURANT => '406',
            self::AVANTAGE_REP_TELEPHONE => "407",
            self::AVANTAGE_REP_INTERNET => '408',
            self::AVANTAGE_REP_GARDINNAGE => '409',

            // --- 5xx : CAHRGE PATRONALE ---
            self::CNPS_VIEILLESSE_SALARIALE => '500',
            self::CNPS_VIEILLESSE_PATRONALE => '500',
            self::CREDIT_FONCIER_SALARIALE => '501',
            self::CREDIT_FONCIER_PATRONALE => '501',
            self::IRPP => '502',
            self::CENTIME_COMMUNAL => '503',
            self::TAXE_DEVELOPPEMENT => '504',
            self::REDEVANCE_AUDIO_VISUELLE => '505',
            self::SYNDICAT => '506',


            self::CNPS_ALLOCATION_FAMILIALE => '600',
            self::CNPS_ACCIDENT_MALADIE_PRO => '601',
            self::FNE => '602',

            self::RETENUE_AVANCE_SALAIRE => '700',
            self::RETENUE_PRET_EMPLOYE => '701',
            self::RETENUE_SANCTION => '702',
            self::SAISIE_SALAIRE => '703',
            self::RETENUE_CANTINE => '704',
            self::ACCOMPTE_SALAIRE => '705',
            self::RETENUE_ABSENCES => '706',

            self::GROSS_SALARY => '800',
            self::INTERMEDIATE_GROSS_SALARY => '801',
            self::TAXABLE_GROSS_SALARY => '802',
            self::CONTRIBUTORY_SALARY => '803',
            self::AVERAGE_SALARY => '804',
            self::SMIC => '805',
            self::NAD => '806',
            self::NAP => '807',
        };
    }
}
