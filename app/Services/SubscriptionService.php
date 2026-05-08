<?php

namespace App\Services;

use App\Enums\FeatureEnum;
use App\Enums\PlanEnum;
use App\Models\Company;
use Illuminate\Support\Carbon;
use Squarhe\Subscription\Models\Plan;
use Squarhe\Subscription\Models\Subscription;

class SubscriptionService
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Souscription / changement de plan
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Souscrit une entreprise à un plan donné (enum).
     * Gère aussi le plan FREE avec son essai de 15 jours.
     */
    public function subscribeTo(Company $company, PlanEnum $planEnum, bool $immediately = true): Subscription
    {
        $plan = $this->resolvePlan($planEnum);

        if ($planEnum === PlanEnum::FREE) {
            // Essai de 15 jours — on fixe l'expiration manuellement.
            return $company->subscribeTo($plan, expiration: now()->addDays(15));
        }

        if ($company->subscription && $company->subscription->exists()) {
            return $company->switchTo($plan, immediately: $immediately);
        }

        return $company->subscribeTo($plan);
    }

    /**
     * Change de plan immédiatement (upgrade / downgrade).
     */
    public function switchPlan(Company $company, PlanEnum $planEnum, bool $immediately = true): Subscription
    {
        $plan = $this->resolvePlan($planEnum);

        return $company->switchTo($plan, immediately: $immediately);
    }

    /**
     * Renouvelle l'abonnement en cours.
     */
    public function renew(Company $company): Subscription
    {
        return $company->subscription->renew();
    }

    /**
     * Annule l'abonnement (accès maintenu jusqu'à expiration + grace days).
     * nb : apres le cancel hasActiveSubscription retournera toujours true
     */
    public function cancel(Company $company): Subscription
    {
        $company->subscription->cancel();

        return $company->subscription;
    }

    /**
     * Supprime immédiatement l'accès (sans attendre l'expiration).
     */
    public function suppress(Company $company): Subscription
    {
        $company->subscription->suppress();

        return $company->subscription;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Vérifications d'état
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * L'entreprise possède-t-elle un abonnement actif (y compris en grace) ?
     */
    public function hasActiveSubscription(Company $company): bool
    {
        return $company->subscription?->exists() ?? false;
    }

    /**
     * L'entreprise peut-elle utiliser une feature donnée ?
     */
    public function canUseFeature(Company $company, FeatureEnum $feature): bool
    {
        return $company->canConsume($feature->value, 1);
    }

    /**
     * L'entreprise peut-elle encore ajouter un employé ?
     *
     * On vérifie si le solde restant de la feature est ≥ 1.
     */
    public function canAddEmployee(Company $company): bool
    {
       
        return $this->hasActiveSubscription($company) && $company->canConsume(FeatureEnum::MAX_EMPLOYEES->value, 1);
    }

    /**
     * L'entreprise peut-elle encore ajouter un administrateur ?
     */
    public function canAddAdmin(Company $company): bool
    {
        return $this->hasActiveSubscription($company) && $company->canConsume(FeatureEnum::MAX_ADMINS->value, 1);
    }

    /**
     * L'entreprise a-t-elle accès à une feature booléenne ?
     * (espace-collaborateur, documents, rapports-avances, support-prioritaire)
     */
    public function hasFeature(Company $company, FeatureEnum $feature): bool
    {
        return $this->hasActiveSubscription($company) && $company->canConsume($feature->value, 0);
    }

    /**
     * Consomme 1 charge de la feature max-employees (appeler à la création d'un employé).
     */
    public function consumeEmployeeSlot(Company $company): void
    {
        $company->consume(FeatureEnum::MAX_EMPLOYEES->value, 1);
    }

    /**
     * Consomme 1 charge de la feature max-employees (appeler à la création d'un employé).
     */
    public function releaseEmployeeSlot(Company $company): void
    {
        $company->getLastConsumption(FeatureEnum::MAX_EMPLOYEES->value)?->delete();
    }

    /**
     * Retourne le nombre de slots employés restants (-1 si illimité / non applicable).
     */
    public function remainingEmployeeSlots(Company $company): int
    {
        if (! $this->hasActiveSubscription($company)) {
            return 0;
        }

        return (int) $company->balance(FeatureEnum::MAX_EMPLOYEES->value);
    }

    /**
     * Retourne le plan actuel de l'entreprise sous forme d'enum, ou null.
     */
    public function currentPlan(Company $company): ?PlanEnum
    {
        $planName = $company->subscription?->plan?->name;

        if (! $planName) {
            return null;
        }

        return PlanEnum::tryFrom($planName);
    }

    /**
     * Retourne la date d'expiration de l'abonnement actif.
     */
    public function expiresAt(Company $company): ?Carbon
    {
        return $company->subscription?->expires_at
            ? Carbon::parse($company->subscription->expires_at)
            : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers utilitaires
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcule le prix total pour un enum de plan.
     * (Encapsule simplement PlanEnum::monthlyPrice() pour faciliter l'injection.)
     */
    public function priceFor(PlanEnum $planEnum): int
    {
        return $planEnum->monthlyPrice();
    }

    /**
     * Retourne le modèle Plan soulbscription correspondant à l'enum.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function resolvePlan(PlanEnum $planEnum): Plan
    {
        return Plan::whereName($planEnum->value)->firstOrFail();
    }

    /**
     * Retourne la liste des plans disponibles structurée pour la vue,
     * regroupée par famille (starter / growth / business).
     *
     * @return array<string, array<int, array{enum: PlanEnum, plan: Plan}>>
     */
    public function availablePlansGrouped(): array
    {
        $result = [];

        foreach (PlanEnum::paidFamilies() as $family => $cases) {
            foreach ($cases as $case) {
                $result[$family][] = [
                    'enum'  => $case,
                    'plan'  => $this->resolvePlan($case),
                ];
            }
        }

        return $result;
    }
}