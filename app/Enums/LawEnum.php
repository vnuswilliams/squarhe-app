<?php

namespace App\Enums;

use App\Concerns\EnumTrait;

enum LawEnum: string
{
    use EnumTrait;

    case LAW_WORK = 'law';
    case COMMERCE = 'convention_commerce';
    case INDUSTRIES_TRANSFORMATION = 'convention_industries_transformation';
    case ASSURANCES = 'convention_assurances';
    case BANQUES_ETABLISSEMENTS_FINANCIERS = 'convention_banques_etablissements_financiers';
    case HYDROCARBURES = 'convention_hydrocarbures';
    case PRODUITS_PETROLIERS = 'convention_produits_petroliers';
    case BATIMENT_TRAVAUX_PUBLICS = 'convention_btp';
    case INDUSTRIES_POLYGRAPHIQUES = 'convention_industries_polygraphiques';
    case PRODUITS_FORESTIERS = 'convention_produits_forestiers';
    case TRANSPORTS_ROUTIERS = 'convention_transports_routiers';
    case TRANSPORTS_URBAINS_INTERURBAINS = 'convention_transports_urbains_interurbains';
    case TRANSPORTS_MARITIMES_TRANSITAIRES = 'convention_transports_maritimes_transitaires';
    case TRANSPORTS_AERIENS = 'convention_transports_aeriens';
    case MANUTENTION_PORTUAIRE = 'convention_manutention_portuaire';
    case SOCIETES_GARDIENNAGE = 'convention_societes_gardiennage';
    case AGRICULTURE = 'convention_agriculture';
    case TELECOMMUNICATIONS = 'convention_telecommunications';
    case HOTELS_RESTAURANTS_CAFES_BARS = 'convention_hotels_restaurants_cafes_bars';
    case HOPITAUX_PUBLICS = 'convention_hopitaux_publics';
    case PHARMACIE = 'convention_pharmacie';
    case DECHETS_ASSAINISSEMENT = 'convention_dechets_assainissement';
    case EAU_POTABLE_ASSAINISSEMENT = 'convention_eau_potable_assainissement_liquide';
    case BOULANGERIES_PATISSERIES = 'convention_boulangeries_patesseries';
 

    public function label(): string
    {
        return match ($this) {
            self::LAW_WORK => 'Droit du travail',
            self::COMMERCE => 'Conv. Coll. nat. du commerce',
            self::INDUSTRIES_TRANSFORMATION => 'Conv. Coll. nat. des industries de transformation',
            self::ASSURANCES => 'Conv. Coll. nat. des assurances',
            self::BANQUES_ETABLISSEMENTS_FINANCIERS => 'Conv. Coll. nat. des banques et établissements financiers',
            self::HYDROCARBURES => 'Conv. Coll. nat. des hydrocarbures (exploration, production, raffinage)',
            self::PRODUITS_PETROLIERS => 'Conv. Coll. nat. des produits pétroliers (stockage et distribution)',
            self::BATIMENT_TRAVAUX_PUBLICS => 'Conv. Coll.  du bâtiment et travaux publics (BTP)',
            self::INDUSTRIES_POLYGRAPHIQUES => 'Conv. Coll.  des industries polygraphiques',
            self::PRODUITS_FORESTIERS => 'Conv. Coll.  des produits forestiers',
            self::TRANSPORTS_ROUTIERS => 'Conv. Coll.  des transports routiers',
            self::TRANSPORTS_URBAINS_INTERURBAINS => 'Conv. Coll.  des transports urbains et interurbains',
            self::TRANSPORTS_MARITIMES_TRANSITAIRES => 'Conv. Coll.  des transports maritimes et transitaires',
            self::TRANSPORTS_AERIENS => 'Conv. Coll.  des transports aériens',
            self::MANUTENTION_PORTUAIRE => 'Conv. Coll.  de la manutention portuaire',
            self::SOCIETES_GARDIENNAGE => 'Conv. Coll.  des sociétés de gardiennage',
            self::AGRICULTURE => 'Conv. Coll.  de l’agriculture',
            self::TELECOMMUNICATIONS => 'Conv. Coll.  des télécommunications',
            self::HOTELS_RESTAURANTS_CAFES_BARS => 'Conv. Coll.  des hôtels, restaurants, cafés et bars',
            self::HOPITAUX_PUBLICS => 'Conv. Coll.  des hôpitaux publics',
            self::PHARMACIE => 'Conv. Coll.  de la pharmacie',
            self::DECHETS_ASSAINISSEMENT => 'Conv. Coll.  des déchets et assainissement',
            self::EAU_POTABLE_ASSAINISSEMENT => 'Conv. Coll.  de l’eau potable et assainissement liquide',
            self::BOULANGERIES_PATISSERIES => 'Conv. Coll.  des boulangeries et pâtisseries',
           
        };
    }

    //
}
