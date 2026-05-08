<?php

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Consumables ────────────────────────────────────────────────────
        //
        // Les features consumables ont une périodicité mensuelle :
        // le quota est réinitialisé à chaque renouvellement de cycle.
        // Le nombre de charges réel (ex. 5, 20, 50…) est défini
        // par plan dans PlanSeeder via la colonne pivot `charges`.

        Feature::firstOrCreate(
            ['name' => FeatureEnum::MAX_EMPLOYEES->value],
            [
                'consumable'       => true,
                'quota'            => false,
                'postpaid'         => false,
                'periodicity_type' => PeriodicityType::Month,
                'periodicity'      => 1,
            ],
        );

        Feature::firstOrCreate(
            ['name' => FeatureEnum::MAX_ADMINS->value],
            [
                'consumable'       => true,
                'quota'            => false,
                'postpaid'         => false,
                'periodicity_type' => PeriodicityType::Month,
                'periodicity'      => 1,
            ],
        );

        // ── 2. Booléens ───────────────────────────────────────────────────────
        //
        // Les features booléennes ne sont pas consumables.
        // Leur simple présence dans la table pivot plan_feature
        // suffit à accorder l'accès — aucun quota à définir.

        $booleanFeatures = [
            FeatureEnum::ESPACE_COLLABORATEUR,
            FeatureEnum::DOCUMENTS,
            FeatureEnum::RAPPORTS_AVANCES,
            FeatureEnum::SUPPORT_PRIORITAIRE,
        ];

        foreach ($booleanFeatures as $feature) {
            Feature::firstOrCreate(
                ['name' => $feature->value],
                [
                    'consumable'       => false,
                    'quota'            => false,
                    'postpaid'         => false,
                    'periodicity_type' => null,
                    'periodicity'      => null,
                ],
            );
        }
    }
}