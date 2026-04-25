<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum IranEnum: string
{
    use EnumTrait;


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

    public function label(): string
    {
        return match ($this) {

            self::AVANTAGE_REP_LOGEMENT => 'Iran - logement',
            self::AVANTAGE_REP_VEHICULE => 'Iran - véhicule',
            self::AVANTAGE_REP_NOURRITURE => 'Iran - nourriture',
            self::AVANTAGE_REP_DOMESTIQUE => 'Iran - domestique',
            self::AVANTAGE_REP_ELECTRICITE => 'Iran - électricité',
            self::AVANTAGE_REP_EAU => 'Iran - eau',
            self::AVANTAGE_REP_CARBURANT => 'Iran - carburant',
            self::AVANTAGE_REP_TELEPHONE => "Iran - téléphone",
            self::AVANTAGE_REP_INTERNET => 'Iran - internet',
            self::AVANTAGE_REP_GARDINNAGE => 'Iran - gardinnage',
        };
    }

    public function taux(): ?float
    {
        return match ($this) {
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
        };
    }
}
