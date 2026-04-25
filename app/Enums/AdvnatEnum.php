<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum AdvnatEnum: string
{
    use EnumTrait;
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

    public function label(): string
    {
        return match ($this) {

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
        };
    }
}
