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
    case INDEMNITE_COMPENSATRISE_CONGE_PAYE = 'indemnite_compensatrise_conge_page';

    // --- 4xx : CHARGES SALARIALES ---
    case CNPS_VIEILLESSE_SALARIALE = 'cnps_pension_vieillesse_salariale';
    case CREDIT_FONCIER_SALARIALE = 'credit_foncier_salariale';
    case CENTIME_COMMUNAL = 'centime_additionnel_communal';
    case IRPP = 'irpp';
    case TAXE_DEVELOPPEMENT = 'taxe_developpement_locale';
    case REDEVANCE_AUDIO_VISUELLE = 'redevance_audio_visuelle';
    case SYNDICAT = 'syndicat';

    // --- 5xx : CHARGES PATRONALES ---
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

    // Indemnités représentatives des avantages en nature
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

    case GROSS_SALARY = 'gross_salary';
    case INTERMEDIATE_GROSS_SALARY = 'intermediate_gross_salary';
    case TAXABLE_GROSS_SALARY = 'taxable_gross_salary';
    case CONTRIBUTORY_SALARY = 'contributory_salary';
    case AVERAGE_SALARY = 'average_salary';
    case SMIC = 'smic';
    case NAD = 'nad';
    case NAP = 'nap';

    // -------------------------------------------------------------------------

    public function label(): string
    {
        return match ($this) {
            self::SALAIRE_BASE                       => __('payslipitem.salaire_base'),
            self::SUR_SALAIRE                        => __('payslipitem.sur_salaire'),
            self::TREIZIEME_MOIS                     => __('payslipitem.treizieme_mois'),
            self::ALLOCATION_CONGE                   => __('payslipitem.allocation_conge'),
            self::HEURE_SUPP                         => __('payslipitem.heure_supp'),

            self::RETENUE_AVANCE_SALAIRE             => __('payslipitem.retenue_avance_salaire'),
            self::RETENUE_PRET_EMPLOYE               => __('payslipitem.retenue_pret_employe'),
            self::RETENUE_SANCTION                   => __('payslipitem.retenue_sanction'),
            self::SAISIE_SALAIRE                     => __('payslipitem.saisie_salaire'),
            self::RETENUE_CANTINE                    => __('payslipitem.retenue_cantine'),
            self::ACCOMPTE_SALAIRE                   => __('payslipitem.accompte_salaire'),
            self::RETENUE_ABSENCES                   => __('payslipitem.retenue_absences'),

            self::PRIME_ANCIENNETE                   => __('payslipitem.prime_anciennete'),
            self::PRIME_RENDEMENT                    => __('payslipitem.prime_rendement'),
            self::PRIME_PRODUCTION                   => __('payslipitem.prime_production'),
            self::PRIME_FONCTION                     => __('payslipitem.prime_fonction'),
            self::PRIME_RISQUE                       => __('payslipitem.prime_risque'),
            self::PRIME_CAISSE                       => __('payslipitem.prime_caisse'),
            self::PRIME_PANIER                       => __('payslipitem.prime_panier'),
            self::PRIME_ASSIDUITE                    => __('payslipitem.prime_assiduite'),
            self::PRIME_INSALUBRITE                  => __('payslipitem.prime_insalubrite'),
            self::PRIME_TECHNICITE                   => __('payslipitem.prime_technicite'),
            self::PRIME_OUTILLAGE                    => __('payslipitem.prime_outillage'),

            self::INDEMNITE_LOGEMENT                 => __('payslipitem.indemnite_logement'),
            self::INDEMNITE_TRANSPORT                => __('payslipitem.indemnite_transport'),
            self::INDEMNITE_SUJETION                 => __('payslipitem.indemnite_sujetion'),
            self::INDEMNITE_REPAS                    => __('payslipitem.indemnite_repas'),
            self::INDEMNITE_INSALUBRITE              => __('payslipitem.indemnite_insalubrite'),
            self::INDEMNITE_SALISSURE                => __('payslipitem.indemnite_salissure'),
            self::INDEMNITE_DEPLACEMENT              => __('payslipitem.indemnite_deplacement'),
            self::INDEMNITE_LAIT                     => __('payslipitem.indemnite_lait'),
            self::INDEMNITE_REPRESENTATION           => __('payslipitem.indemnite_representation'),
            self::INDEMNITE_BICYCLETTE               => __('payslipitem.indemnite_bicyclette'),
            self::INDEMNITE_SECURITE                 => __('payslipitem.indemnite_securite'),
            self::INDEMNITE_USAGE_VEHICULE           => __('payslipitem.indemnite_usage_vehicule'),
            self::INDEMNITE_LICENCIEMENT             => __('payslipitem.indemnite_licenciement'),
            self::INDEMNITE_PREAVIS                  => __('payslipitem.indemnite_preavis'),
            self::INDEMNITE_CHOMAGE_TECHNIQUE        => __('payslipitem.indemnite_chomage_technique'),
            self::INDEMNITE_COMPENSATRISE_CONGE_PAYE => __('payslipitem.indemnite_compensatrice_conge_paye'),

            self::IRPP                               => __('payslipitem.irpp'),
            self::CENTIME_COMMUNAL                   => __('payslipitem.centime_communal'),
            self::CREDIT_FONCIER_SALARIALE           => __('payslipitem.credit_foncier_salariale'),
            self::TAXE_DEVELOPPEMENT                 => __('payslipitem.taxe_developpement'),
            self::REDEVANCE_AUDIO_VISUELLE           => __('payslipitem.redevance_audio_visuelle'),
            self::SYNDICAT                           => __('payslipitem.syndicat'),

            self::CNPS_VIEILLESSE_SALARIALE          => __('payslipitem.cnps_vieillesse_salariale'),
            self::CNPS_VIEILLESSE_PATRONALE          => __('payslipitem.cnps_vieillesse_patronale'),
            self::CNPS_ALLOCATION_FAMILIALE          => __('payslipitem.cnps_allocation_familiale'),
            self::CNPS_ACCIDENT_MALADIE_PRO          => __('payslipitem.cnps_accident_maladie_pro'),
            self::CREDIT_FONCIER_PATRONALE           => __('payslipitem.credit_foncier_patronale'),
            self::FNE                                => __('payslipitem.fne'),

            self::AVANTAGE_LOGEMENT                  => __('payslipitem.avantage_logement'),
            self::AVANTAGE_VEHICULE                  => __('payslipitem.avantage_vehicule'),
            self::AVANTAGE_NOURRITURE                => __('payslipitem.avantage_nourriture'),
            self::AVANTAGE_DOMESTIQUE                => __('payslipitem.avantage_domestique'),
            self::AVANTAGE_ELECTRICITE               => __('payslipitem.avantage_electricite'),
            self::AVANTAGE_EAU                       => __('payslipitem.avantage_eau'),
            self::AVANTAGE_CARBURANT                 => __('payslipitem.avantage_carburant'),
            self::AVANTAGE_TELEPHONE                 => __('payslipitem.avantage_telephone'),
            self::AVANTAGE_INTERNET                  => __('payslipitem.avantage_internet'),
            self::AVANTAGE_GARDINNAGE                => __('payslipitem.avantage_gardinnage'),

            self::AVANTAGE_REP_LOGEMENT              => __('payslipitem.avantage_rep_logement'),
            self::AVANTAGE_REP_VEHICULE              => __('payslipitem.avantage_rep_vehicule'),
            self::AVANTAGE_REP_NOURRITURE            => __('payslipitem.avantage_rep_nourriture'),
            self::AVANTAGE_REP_DOMESTIQUE            => __('payslipitem.avantage_rep_domestique'),
            self::AVANTAGE_REP_ELECTRICITE           => __('payslipitem.avantage_rep_electricite'),
            self::AVANTAGE_REP_EAU                   => __('payslipitem.avantage_rep_eau'),
            self::AVANTAGE_REP_CARBURANT             => __('payslipitem.avantage_rep_carburant'),
            self::AVANTAGE_REP_TELEPHONE             => __('payslipitem.avantage_rep_telephone'),
            self::AVANTAGE_REP_INTERNET              => __('payslipitem.avantage_rep_internet'),
            self::AVANTAGE_REP_GARDINNAGE            => __('payslipitem.avantage_rep_gardinnage'),

            self::GROSS_SALARY                       => __('payslipitem.gross_salary'),
            self::INTERMEDIATE_GROSS_SALARY          => __('payslipitem.intermediate_gross_salary'),
            self::TAXABLE_GROSS_SALARY               => __('payslipitem.taxable_gross_salary'),
            self::CONTRIBUTORY_SALARY                => __('payslipitem.contributory_salary'),
            self::AVERAGE_SALARY                     => __('payslipitem.average_salary'),
            self::SMIC                               => __('payslipitem.smic'),
            self::NAD                                => __('payslipitem.nad'),
            self::NAP                                => __('payslipitem.nap'),
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
            self::PRIME_TECHNICITE => '110',

            self::INDEMNITE_PREAVIS => '200',
            self::INDEMNITE_LICENCIEMENT => '201',
            self::INDEMNITE_CHOMAGE_TECHNIQUE => '202',
            self::INDEMNITE_COMPENSATRISE_CONGE_PAYE => '203',
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
            self::INDEMNITE_REPAS => '215',

            self::AVANTAGE_LOGEMENT => '300',
            self::AVANTAGE_VEHICULE => '301',
            self::AVANTAGE_NOURRITURE => '302',
            self::AVANTAGE_DOMESTIQUE => '303',
            self::AVANTAGE_ELECTRICITE => '304',
            self::AVANTAGE_EAU => '305',
            self::AVANTAGE_CARBURANT => '306',
            self::AVANTAGE_TELEPHONE => '307',
            self::AVANTAGE_INTERNET => '308',
            self::AVANTAGE_GARDINNAGE => '309',

            self::AVANTAGE_REP_LOGEMENT => '400',
            self::AVANTAGE_REP_VEHICULE => '401',
            self::AVANTAGE_REP_NOURRITURE => '402',
            self::AVANTAGE_REP_DOMESTIQUE => '403',
            self::AVANTAGE_REP_ELECTRICITE => '404',
            self::AVANTAGE_REP_EAU => '405',
            self::AVANTAGE_REP_CARBURANT => '406',
            self::AVANTAGE_REP_TELEPHONE => '407',
            self::AVANTAGE_REP_INTERNET => '408',
            self::AVANTAGE_REP_GARDINNAGE => '409',

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