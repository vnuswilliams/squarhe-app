<?php

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use App\Enums\PlanEnum;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les features consumables une seule fois
        $employeeFeature = Feature::whereName(FeatureEnum::MAX_EMPLOYEES->value)->firstOrFail();
        $adminFeature    = Feature::whereName(FeatureEnum::MAX_ADMINS->value)->firstOrFail();

        // Index des features booléennes par value pour lookup rapide
        $booleanFeatures = Feature::whereIn('name', array_map(
            fn ($f) => $f->value,
            FeatureEnum::booleans(),
        ))->get()->keyBy('name');

        foreach (PlanEnum::cases() as $planEnum) {

            /** @var Plan $plan */
            $plan = Plan::updateOrCreate(
                ['name' => $planEnum->value],
                [
                    'periodicity_type' => $planEnum->periodicityType(),
                    'periodicity'      => $planEnum->periodicity(),
                    'grace_days'       => $planEnum->graceDays(),
                ],
            );

            // ── Consumables : attachés à TOUS les plans avec leurs charges ────
            $plan->features()->syncWithoutDetaching([
                $employeeFeature->id => ['charges' => $planEnum->maxEmployees()],
                $adminFeature->id    => ['charges' => $planEnum->maxAdmins()],
            ]);

            // ── Booléens : attachés uniquement aux plans qui y ont droit ──────
            //
            // PlanEnum::booleanFeatures() retourne la liste des FeatureEnum
            // booléennes pour cette famille de plan.
            // Les plans FREE et Starter retournent [] → rien n'est attaché
            // → canUse() retourne false automatiquement via soulbscription.

            foreach ($planEnum->booleanFeatures() as $featureEnum) {
                $feature = $booleanFeatures->get($featureEnum->value);

                if (! $feature) {
                    continue; // sécurité si un feature n'a pas été seedé
                }

                $plan->features()->syncWithoutDetaching([
                    $feature->id => ['charges' => null],
                ]);
            }
        }
    }
}