<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum RemunerationEnum: string
{
    use EnumTrait;

    // --- 0xx : SALAIRES & ALLOCATIONS ---
    case SALAIRE_BASE = 'salaire_base';
    case SUR_SALAIRE = 'sursalaire';
    case TREIZIEME_MOIS = '13eme_mois';
    case ALLOCATION_CONGE = 'allocation_conge';
    case ALLOCATION_CONGE_SUPPLEMENTAIRE = 'allocation_conge_supplementaire';
    case HEURE_SUPP_120 = 'heure_supp_120';
    case HEURE_SUPP_130 = 'heure_supp_130';
    case HEURE_SUPP_140 = 'heure_supp_140';
    case HEURE_SUPP_150 = 'heure_supp_150';
    case HEURE_SUPP_200 = 'heure_supp_200';
    case HEURE_SUPP = 'heure_supp';

    // --- 1xx : RETENUES ---
    case RETENUE_AVANCE_SALAIRE = 'retenue_avance_salaire';
    case RETENUE_PRET_EMPLOYE = 'retenue_pret_employe';
    case RETENUE_SANCTION = 'retenue_sanction_disciplinaire';
    case SAISIE_SALAIRE = 'saisie_salaire';
    case RETENUE_CANTINE = 'retenue_cantine';
    case ACCOMPTE_SALAIRE = 'accompte_salaire';
    case RETENUE_ABSENCES = 'retenues_absences';

    // --- 2xx : PRIMES ---
    case PRIME_INSALUBRITE = 'PRIME_insalubrite';
    case PRIME_ANCIENNETE = 'prime_anciennete';
    case PRIME_RENDEMENT = 'prime_rendement';
    case PRIME_TECHNICITE = 'prime_technicite';
    case PRIME_PRODUCTION = 'prime_production';
    case PRIME_FONCTION = 'prime_fonction';
    case PRIME_RISQUE = 'prime_risque';
    case PRIME_CAISSE = 'prime_caisse';
    case PRIME_PANIER = 'prime_panier';
    case PRIME_ASSIDUITE = 'prime_assiduite';
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

    // --- 4xx : IMPÔTS ---
    case IRPP = 'irpp';
    case CENTIME_COMMUNAL = 'centime_additionnel_communal';
    case FNE = 'fond_national_emploi';
    case CREDIT_FONCIER = 'credit_foncier';
    case TAXE_DEVELOPPEMENT = 'taxe_developpement_locale';
    case REDEVANCE_AUDIO_VISUELLE = 'redevance_audio_visuelle';
    case SYNDICAT = 'syndicat';

    // --- 5xx : COTISATIONS CNPS ---
    case CNPS_VIEILLESSE_SALARIALE = 'cnps_pension_vieillesse_salariale';
    case CNPS_VIEILLESSE_PATRONALE = 'cnps_pension_vieillesse_patronale';
    case CNPS_ALLOCATION_FAMILIALE = 'cnps_allocation_familiale';
    case CNPS_ACCIDENT_MALADIE_PRO = 'cnps_accident_maladie_pro';

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

    // -------------------------------------------------------------------------

    public static function forSelect(): array
    {
        return [
            self::SUR_SALAIRE,
            self::TREIZIEME_MOIS,
            self::PRIME_INSALUBRITE,

            self::RETENUE_AVANCE_SALAIRE,
            self::RETENUE_PRET_EMPLOYE,
            self::RETENUE_SANCTION,
            self::SAISIE_SALAIRE,
            self::RETENUE_CANTINE,
            self::ACCOMPTE_SALAIRE,

            self::PRIME_RENDEMENT,
            self::PRIME_PRODUCTION,
            self::PRIME_FONCTION,
            self::PRIME_RISQUE,
            self::PRIME_CAISSE,
            self::PRIME_PANIER,
            self::PRIME_ASSIDUITE,
            self::PRIME_TECHNICITE,
            self::PRIME_OUTILLAGE,

            self::INDEMNITE_SALISSURE,
            self::INDEMNITE_DEPLACEMENT,
            self::INDEMNITE_LAIT,
            self::INDEMNITE_REPRESENTATION,
            self::INDEMNITE_BICYCLETTE,
            self::INDEMNITE_SECURITE,
            self::INDEMNITE_USAGE_VEHICULE,

            self::AVANTAGE_LOGEMENT,
            self::AVANTAGE_VEHICULE,
            self::AVANTAGE_NOURRITURE,
            self::AVANTAGE_DOMESTIQUE,
            self::AVANTAGE_ELECTRICITE,
            self::AVANTAGE_EAU,
            self::AVANTAGE_CARBURANT,
            self::AVANTAGE_TELEPHONE,
            self::AVANTAGE_INTERNET,
            self::AVANTAGE_GARDINNAGE,

            self::AVANTAGE_REP_LOGEMENT,
            self::AVANTAGE_REP_VEHICULE,
            self::AVANTAGE_REP_NOURRITURE,
            self::AVANTAGE_REP_DOMESTIQUE,
            self::AVANTAGE_REP_ELECTRICITE,
            self::AVANTAGE_REP_EAU,
            self::AVANTAGE_REP_CARBURANT,
            self::AVANTAGE_REP_TELEPHONE,
            self::AVANTAGE_REP_INTERNET,
            self::AVANTAGE_REP_GARDINNAGE,
        ];
    }

    public function type()
    {
        return match ($this) {
            self::SUR_SALAIRE => RemunerationTypeEnum::AUTRE->value,
            self::TREIZIEME_MOIS => RemunerationTypeEnum::AUTRE->value,

            self::PRIME_INSALUBRITE => RemunerationTypeEnum::PRIME->value,

            self::RETENUE_AVANCE_SALAIRE => RemunerationTypeEnum::RETENU->value,
            self::RETENUE_PRET_EMPLOYE => RemunerationTypeEnum::RETENU->value,
            self::RETENUE_SANCTION => RemunerationTypeEnum::RETENU->value,
            self::SAISIE_SALAIRE => RemunerationTypeEnum::RETENU->value,
            self::RETENUE_CANTINE => RemunerationTypeEnum::RETENU->value,
            self::ACCOMPTE_SALAIRE => RemunerationTypeEnum::RETENU->value,

            self::PRIME_RENDEMENT => RemunerationTypeEnum::PRIME->value,
            self::PRIME_PRODUCTION => RemunerationTypeEnum::PRIME->value,
            self::PRIME_FONCTION => RemunerationTypeEnum::PRIME->value,
            self::PRIME_RISQUE => RemunerationTypeEnum::PRIME->value,
            self::PRIME_CAISSE => RemunerationTypeEnum::PRIME->value,
            self::PRIME_PANIER => RemunerationTypeEnum::PRIME->value,
            self::PRIME_ASSIDUITE => RemunerationTypeEnum::PRIME->value,
            self::PRIME_TECHNICITE => RemunerationTypeEnum::PRIME->value,
            self::PRIME_OUTILLAGE => RemunerationTypeEnum::PRIME->value,

            self::INDEMNITE_SALISSURE => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_DEPLACEMENT => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_LAIT => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_REPRESENTATION => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_BICYCLETTE => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_SECURITE => RemunerationTypeEnum::INDEMNITE->value,
            self::INDEMNITE_USAGE_VEHICULE => RemunerationTypeEnum::INDEMNITE->value,

            self::AVANTAGE_LOGEMENT => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_VEHICULE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_NOURRITURE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_DOMESTIQUE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_ELECTRICITE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_EAU => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_CARBURANT => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_TELEPHONE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_INTERNET => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_GARDINNAGE => RemunerationTypeEnum::ADVANTAGE->value,

            self::AVANTAGE_REP_LOGEMENT => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_VEHICULE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_NOURRITURE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_DOMESTIQUE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_ELECTRICITE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_EAU => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_CARBURANT => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_TELEPHONE => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_INTERNET => RemunerationTypeEnum::ADVANTAGE->value,
            self::AVANTAGE_REP_GARDINNAGE => RemunerationTypeEnum::ADVANTAGE->value,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SALAIRE_BASE                       => __('remuneration.salaire_base'),
            self::SUR_SALAIRE                        => __('remuneration.sur_salaire'),
            self::TREIZIEME_MOIS                     => __('remuneration.treizieme_mois'),
            self::ALLOCATION_CONGE                   => __('remuneration.allocation_conge'),
            self::ALLOCATION_CONGE_SUPPLEMENTAIRE    => __('remuneration.allocation_conge_supplementaire'),
            self::HEURE_SUPP_120                     => __('remuneration.heure_supp_120'),
            self::HEURE_SUPP_130                     => __('remuneration.heure_supp_130'),
            self::HEURE_SUPP_140                     => __('remuneration.heure_supp_140'),
            self::HEURE_SUPP_150                     => __('remuneration.heure_supp_150'),
            self::HEURE_SUPP_200                     => __('remuneration.heure_supp_200'),
            self::HEURE_SUPP                         => __('remuneration.heure_supp'),

            self::RETENUE_AVANCE_SALAIRE             => __('remuneration.retenue_avance_salaire'),
            self::RETENUE_PRET_EMPLOYE               => __('remuneration.retenue_pret_employe'),
            self::RETENUE_SANCTION                   => __('remuneration.retenue_sanction'),
            self::SAISIE_SALAIRE                     => __('remuneration.saisie_salaire'),
            self::RETENUE_CANTINE                    => __('remuneration.retenue_cantine'),
            self::ACCOMPTE_SALAIRE                   => __('remuneration.accompte_salaire'),
            self::RETENUE_ABSENCES                   => __('remuneration.retenue_absences'),

            self::PRIME_INSALUBRITE                  => __('remuneration.prime_insalubrite'),
            self::PRIME_ANCIENNETE                   => __('remuneration.prime_anciennete'),
            self::PRIME_RENDEMENT                    => __('remuneration.prime_rendement'),
            self::PRIME_TECHNICITE                   => __('remuneration.prime_technicite'),
            self::PRIME_PRODUCTION                   => __('remuneration.prime_production'),
            self::PRIME_FONCTION                     => __('remuneration.prime_fonction'),
            self::PRIME_RISQUE                       => __('remuneration.prime_risque'),
            self::PRIME_CAISSE                       => __('remuneration.prime_caisse'),
            self::PRIME_PANIER                       => __('remuneration.prime_panier'),
            self::PRIME_ASSIDUITE                    => __('remuneration.prime_assiduite'),
            self::PRIME_OUTILLAGE                    => __('remuneration.prime_outillage'),

            self::INDEMNITE_LOGEMENT                 => __('remuneration.indemnite_logement'),
            self::INDEMNITE_TRANSPORT                => __('remuneration.indemnite_transport'),
            self::INDEMNITE_SUJETION                 => __('remuneration.indemnite_sujetion'),
            self::INDEMNITE_REPAS                    => __('remuneration.indemnite_repas'),
            self::INDEMNITE_INSALUBRITE              => __('remuneration.indemnite_insalubrite'),
            self::INDEMNITE_SALISSURE                => __('remuneration.indemnite_salissure'),
            self::INDEMNITE_DEPLACEMENT              => __('remuneration.indemnite_deplacement'),
            self::INDEMNITE_LAIT                     => __('remuneration.indemnite_lait'),
            self::INDEMNITE_REPRESENTATION           => __('remuneration.indemnite_representation'),
            self::INDEMNITE_BICYCLETTE               => __('remuneration.indemnite_bicyclette'),
            self::INDEMNITE_SECURITE                 => __('remuneration.indemnite_securite'),
            self::INDEMNITE_USAGE_VEHICULE           => __('remuneration.indemnite_usage_vehicule'),
            self::INDEMNITE_LICENCIEMENT             => __('remuneration.indemnite_licenciement'),
            self::INDEMNITE_PREAVIS                  => __('remuneration.indemnite_preavis'),
            self::INDEMNITE_CHOMAGE_TECHNIQUE        => __('remuneration.indemnite_chomage_technique'),
            self::INDEMNITE_COMPENSATRISE_CONGE_PAYE => __('remuneration.indemnite_compensatrice_conge_paye'),

            self::IRPP                               => __('remuneration.irpp'),
            self::CENTIME_COMMUNAL                   => __('remuneration.centime_communal'),
            self::FNE                                => __('remuneration.fne'),
            self::CREDIT_FONCIER                     => __('remuneration.credit_foncier'),
            self::TAXE_DEVELOPPEMENT                 => __('remuneration.taxe_developpement'),
            self::REDEVANCE_AUDIO_VISUELLE           => __('remuneration.redevance_audio_visuelle'),
            self::SYNDICAT                           => __('remuneration.syndicat'),

            self::CNPS_VIEILLESSE_SALARIALE          => __('remuneration.cnps_vieillesse_salariale'),
            self::CNPS_VIEILLESSE_PATRONALE          => __('remuneration.cnps_vieillesse_patronale'),
            self::CNPS_ALLOCATION_FAMILIALE          => __('remuneration.cnps_allocation_familiale'),
            self::CNPS_ACCIDENT_MALADIE_PRO          => __('remuneration.cnps_accident_maladie_pro'),

            self::AVANTAGE_LOGEMENT                  => __('remuneration.avantage_logement'),
            self::AVANTAGE_VEHICULE                  => __('remuneration.avantage_vehicule'),
            self::AVANTAGE_NOURRITURE                => __('remuneration.avantage_nourriture'),
            self::AVANTAGE_DOMESTIQUE                => __('remuneration.avantage_domestique'),
            self::AVANTAGE_ELECTRICITE               => __('remuneration.avantage_electricite'),
            self::AVANTAGE_EAU                       => __('remuneration.avantage_eau'),
            self::AVANTAGE_CARBURANT                 => __('remuneration.avantage_carburant'),
            self::AVANTAGE_TELEPHONE                 => __('remuneration.avantage_telephone'),
            self::AVANTAGE_INTERNET                  => __('remuneration.avantage_internet'),
            self::AVANTAGE_GARDINNAGE                => __('remuneration.avantage_gardinnage'),

            self::AVANTAGE_REP_LOGEMENT              => __('remuneration.avantage_rep_logement'),
            self::AVANTAGE_REP_VEHICULE              => __('remuneration.avantage_rep_vehicule'),
            self::AVANTAGE_REP_NOURRITURE            => __('remuneration.avantage_rep_nourriture'),
            self::AVANTAGE_REP_DOMESTIQUE            => __('remuneration.avantage_rep_domestique'),
            self::AVANTAGE_REP_ELECTRICITE           => __('remuneration.avantage_rep_electricite'),
            self::AVANTAGE_REP_EAU                   => __('remuneration.avantage_rep_eau'),
            self::AVANTAGE_REP_CARBURANT             => __('remuneration.avantage_rep_carburant'),
            self::AVANTAGE_REP_TELEPHONE             => __('remuneration.avantage_rep_telephone'),
            self::AVANTAGE_REP_INTERNET              => __('remuneration.avantage_rep_internet'),
            self::AVANTAGE_REP_GARDINNAGE            => __('remuneration.avantage_rep_gardinnage'),
        };
    }

    public function taux(): ?float
    {
        return match ($this) {

            self::AVANTAGE_LOGEMENT => 0.15,
            self::AVANTAGE_VEHICULE => 0.10,
            self::AVANTAGE_NOURRITURE => 0.10,
            self::AVANTAGE_DOMESTIQUE => 0.05,
            self::AVANTAGE_ELECTRICITE => 0.04,
            self::AVANTAGE_EAU => 0.02,
            self::AVANTAGE_CARBURANT => 0.10,
            self::AVANTAGE_TELEPHONE => 0.05,
            self::AVANTAGE_INTERNET => 0.05,
            self::AVANTAGE_GARDINNAGE => 0.05,

            self::AVANTAGE_REP_LOGEMENT => 0.15,
            self::AVANTAGE_REP_VEHICULE => 0.10,
            self::AVANTAGE_REP_NOURRITURE => 0.10,
            self::AVANTAGE_REP_DOMESTIQUE => 0.05,
            self::AVANTAGE_REP_ELECTRICITE => 0.04,
            self::AVANTAGE_REP_EAU => 0.02,
            self::AVANTAGE_REP_CARBURANT => 0.10,
            self::AVANTAGE_REP_TELEPHONE => 0.05,
            self::AVANTAGE_REP_INTERNET => 0.05,
            self::AVANTAGE_REP_GARDINNAGE => 0.05,

            default => null,
        };
    }

    public function code(): string
    {
        return match ($this) {

            self::SALAIRE_BASE => PayslipItemsEnum::SALAIRE_BASE->code(),
            self::SUR_SALAIRE => PayslipItemsEnum::SUR_SALAIRE->code(),
            self::TREIZIEME_MOIS => PayslipItemsEnum::TREIZIEME_MOIS->code(),
            self::ALLOCATION_CONGE => PayslipItemsEnum::ALLOCATION_CONGE->code(),
            self::HEURE_SUPP => PayslipItemsEnum::HEURE_SUPP->code(),

            self::PRIME_ANCIENNETE => PayslipItemsEnum::PRIME_ANCIENNETE->code(),
            self::PRIME_RENDEMENT => PayslipItemsEnum::PRIME_RENDEMENT->code(),
            self::PRIME_PRODUCTION => PayslipItemsEnum::PRIME_PRODUCTION->code(),
            self::PRIME_FONCTION => PayslipItemsEnum::PRIME_FONCTION->code(),
            self::PRIME_RISQUE => PayslipItemsEnum::PRIME_RISQUE->code(),
            self::PRIME_CAISSE => PayslipItemsEnum::PRIME_CAISSE->code(),
            self::PRIME_PANIER => PayslipItemsEnum::PRIME_PANIER->code(),
            self::PRIME_ASSIDUITE => PayslipItemsEnum::PRIME_ASSIDUITE->code(),
            self::PRIME_TECHNICITE => PayslipItemsEnum::PRIME_TECHNICITE->code(),
            self::PRIME_OUTILLAGE => PayslipItemsEnum::PRIME_OUTILLAGE->code(),
            self::PRIME_INSALUBRITE => PayslipItemsEnum::PRIME_INSALUBRITE->code(),

            self::INDEMNITE_LOGEMENT => PayslipItemsEnum::INDEMNITE_LOGEMENT->code(),
            self::INDEMNITE_TRANSPORT => PayslipItemsEnum::INDEMNITE_TRANSPORT->code(),
            self::INDEMNITE_SUJETION => PayslipItemsEnum::INDEMNITE_SUJETION->code(),
            self::INDEMNITE_REPAS => PayslipItemsEnum::INDEMNITE_REPAS->code(),
            self::INDEMNITE_INSALUBRITE => PayslipItemsEnum::INDEMNITE_INSALUBRITE->code(),
            self::INDEMNITE_SALISSURE => PayslipItemsEnum::INDEMNITE_SALISSURE->code(),
            self::INDEMNITE_DEPLACEMENT => PayslipItemsEnum::INDEMNITE_DEPLACEMENT->code(),
            self::INDEMNITE_LAIT => PayslipItemsEnum::INDEMNITE_LAIT->code(),
            self::INDEMNITE_REPRESENTATION => PayslipItemsEnum::INDEMNITE_REPRESENTATION->code(),
            self::INDEMNITE_BICYCLETTE => PayslipItemsEnum::INDEMNITE_BICYCLETTE->code(),
            self::INDEMNITE_SECURITE => PayslipItemsEnum::INDEMNITE_SECURITE->code(),
            self::INDEMNITE_USAGE_VEHICULE => PayslipItemsEnum::INDEMNITE_USAGE_VEHICULE->code(),
            self::INDEMNITE_LICENCIEMENT => PayslipItemsEnum::INDEMNITE_LICENCIEMENT->code(),
            self::INDEMNITE_PREAVIS => PayslipItemsEnum::INDEMNITE_PREAVIS->code(),
            self::INDEMNITE_CHOMAGE_TECHNIQUE => PayslipItemsEnum::INDEMNITE_CHOMAGE_TECHNIQUE->code(),
            self::INDEMNITE_COMPENSATRISE_CONGE_PAYE => PayslipItemsEnum::INDEMNITE_COMPENSATRISE_CONGE_PAYE->code(),

            self::AVANTAGE_LOGEMENT => PayslipItemsEnum::AVANTAGE_LOGEMENT->code(),
            self::AVANTAGE_VEHICULE => PayslipItemsEnum::AVANTAGE_VEHICULE->code(),
            self::AVANTAGE_NOURRITURE => PayslipItemsEnum::AVANTAGE_NOURRITURE->code(),
            self::AVANTAGE_DOMESTIQUE => PayslipItemsEnum::AVANTAGE_DOMESTIQUE->code(),
            self::AVANTAGE_ELECTRICITE => PayslipItemsEnum::AVANTAGE_ELECTRICITE->code(),
            self::AVANTAGE_EAU => PayslipItemsEnum::AVANTAGE_EAU->code(),
            self::AVANTAGE_CARBURANT => PayslipItemsEnum::AVANTAGE_CARBURANT->code(),
            self::AVANTAGE_TELEPHONE => PayslipItemsEnum::AVANTAGE_TELEPHONE->code(),
            self::AVANTAGE_INTERNET => PayslipItemsEnum::AVANTAGE_INTERNET->code(),
            self::AVANTAGE_GARDINNAGE => PayslipItemsEnum::AVANTAGE_GARDINNAGE->code(),

            self::AVANTAGE_REP_LOGEMENT => PayslipItemsEnum::AVANTAGE_REP_LOGEMENT->code(),
            self::AVANTAGE_REP_VEHICULE => PayslipItemsEnum::AVANTAGE_REP_VEHICULE->code(),
            self::AVANTAGE_REP_NOURRITURE => PayslipItemsEnum::AVANTAGE_REP_NOURRITURE->code(),
            self::AVANTAGE_REP_DOMESTIQUE => PayslipItemsEnum::AVANTAGE_REP_DOMESTIQUE->code(),
            self::AVANTAGE_REP_ELECTRICITE => PayslipItemsEnum::AVANTAGE_REP_ELECTRICITE->code(),
            self::AVANTAGE_REP_EAU => PayslipItemsEnum::AVANTAGE_REP_EAU->code(),
            self::AVANTAGE_REP_CARBURANT => PayslipItemsEnum::AVANTAGE_REP_CARBURANT->code(),
            self::AVANTAGE_REP_TELEPHONE => PayslipItemsEnum::AVANTAGE_REP_TELEPHONE->code(),
            self::AVANTAGE_REP_INTERNET => PayslipItemsEnum::AVANTAGE_REP_INTERNET->code(),
            self::AVANTAGE_REP_GARDINNAGE => PayslipItemsEnum::AVANTAGE_REP_GARDINNAGE->code(),

            self::RETENUE_AVANCE_SALAIRE => PayslipItemsEnum::RETENUE_AVANCE_SALAIRE->code(),
            self::RETENUE_PRET_EMPLOYE => PayslipItemsEnum::RETENUE_PRET_EMPLOYE->code(),
            self::RETENUE_SANCTION => PayslipItemsEnum::RETENUE_SANCTION->code(),
            self::SAISIE_SALAIRE => PayslipItemsEnum::SAISIE_SALAIRE->code(),
            self::RETENUE_CANTINE => PayslipItemsEnum::RETENUE_CANTINE->code(),
            self::ACCOMPTE_SALAIRE => PayslipItemsEnum::ACCOMPTE_SALAIRE->code(),
            self::RETENUE_ABSENCES => PayslipItemsEnum::RETENUE_ABSENCES->code(),
        };
    }
}