<?php

namespace App\Policies;

use App\Enums\CompanyRoleEnum;
use App\Enums\FeatureEnum;
use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionService;

/**
 * Policy de souscription.
 *
 * Usage dans les contrôleurs :
 *   $this->authorize('accessApp', $company);
 *   $this->authorize('addEmployee', $company);
 *   $this->authorize('useFeature', [FeatureEnum::MAX_EMPLOYEES, $company]);
 *
 * Usage dans les Blade :
 *   @can('accessApp', $company) … @endcan
 */
class SubscriptionPolicy
{
    public function __construct(private readonly SubscriptionService $subscriptions)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Accès global à l'application
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * L'utilisateur peut accéder à son espace si la société a un abonnement actif.
     * C'est le garde-fou principal — à vérifier dans le middleware / layout.
     */
    public function accessApp(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasActiveSubscription($company);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gestion des employés
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * L'utilisateur peut créer un nouvel employé si son quota n'est pas atteint.
     */
    public function addEmployee(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && app(SubscriptionService::class)->canAddEmployee($company);
    }

    /**
     * L'utilisateur peut consulter / modifier un employé existant (quota non vérifié).
     */
    public function manageEmployee(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasActiveSubscription($company);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Gestion des administrateurs
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * L'utilisateur peut inviter un nouvel administrateur.
     */
    public function addAdmin(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->canAddAdmin($company);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Features booléennes — Croissance + Business
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Accès à l'espace collaborateur (portail self-service employé).
     * Disponible : Croissance + Business.
     */
    public function accessEspaceCollaborateur(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasFeature($company, FeatureEnum::ESPACE_COLLABORATEUR);
    }

    /**
     * Accès à la gestion documentaire.
     * Disponible : Croissance + Business.
     */
    public function accessDocuments(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasFeature($company, FeatureEnum::DOCUMENTS);
    }

    /**
     * Accès aux rapports avancés.
     * Disponible : Croissance + Business.
     */
    public function accessRapportsAvances(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasFeature($company, FeatureEnum::RAPPORTS_AVANCES);
    }

    /**
     * Accès au support prioritaire.
     * Disponible : Business uniquement.
     */
    public function accessSupportPrioritaire(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->hasFeature($company, FeatureEnum::SUPPORT_PRIORITAIRE);
    }

    
    // ─────────────────────────────────────────────────────────────────────────
    //  Souscription elle-même
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * L'utilisateur peut voir la page de choix d'offre
     * (toujours autorisé, même sans abonnement actif).
     */
   /* public function viewPlans(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company);
    }*/

    /**
     * L'utilisateur peut souscrire / changer de plan.
     */
   /* public function subscribe(User $user, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company);
    }*/

    /**
     * L'utilisateur peut annuler son abonnement.
     */
    public function cancelSubscription(User $user): bool
    {
        return $user->hasRole(CompanyRoleEn4ADDum::OWNER->value) || $user->hasRole(CompanyRoleEnum::ADMIN->value);;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Feature générique (pour features futures)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Vérifie dynamiquement si une feature est accessible.
     *
     * Usage :
     *   $this->authorize('useFeature', [FeatureEnum::API_ACCESS, $company]);
     */
    public function useFeature(User $user, FeatureEnum $feature, Company $company): bool
    {
        return $this->userBelongsToCompany($user, $company)
            && $this->subscriptions->canUseFeature($company, $feature);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helper privé
    // ─────────────────────────────────────────────────────────────────────────

    private function userBelongsToCompany(User $user, Company $company): bool
    {
        return $user->company_id === $company->id;
    }
}