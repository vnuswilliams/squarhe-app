<?php

namespace App\Enums;

enum FeatureEnum: string
{
    // ── Consumables ───────────────────────────────────────────────────────────

    /**
     * Nombre maximal d'employés actifs autorisés.
     * Charges définies par tranche dans PlanEnum::maxEmployees().
     */
    case MAX_EMPLOYEES = 'max-employees';

    /**
     * Nombre maximal d'administrateurs du compte.
     * FREE + Starter = 1, Croissance = 2, Business = 5.
     */
    case MAX_ADMINS = 'max-admins';

    // ── Booléens (non consumables) ────────────────────────────────────────────

    /**
     * Accès à l'espace collaborateur (portail self-service employé).
     * Disponible : Croissance + Business uniquement.
     */
    case ESPACE_COLLABORATEUR = 'espace-collaborateur';

    /**
     * Accès à la GED / gestion documentaire.
     * Disponible : Croissance + Business uniquement.
     */
    case DOCUMENTS = 'documents';

    /**
     * Accès aux rapports avancés (dashboard analytique RH, masse salariale…).
     * Disponible : Croissance + Business.
     */
    case RAPPORTS_AVANCES = 'rapports-avances';

    /**
     * Support prioritaire (SLA renforcé, canal dédié).
     * Disponible : Business uniquement.
     */
    case SUPPORT_PRIORITAIRE = 'support-prioritaire';

    // ─────────────────────────────────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::MAX_EMPLOYEES        => "Nombre maximal d'employés",
            self::MAX_ADMINS           => "Nombre maximal d'administrateurs",
            self::ESPACE_COLLABORATEUR => 'Espace collaborateur',
            self::DOCUMENTS            => 'Gestion documentaire',
            self::RAPPORTS_AVANCES     => 'Rapports avancés',
            self::SUPPORT_PRIORITAIRE  => 'Support prioritaire',
        };
    }

    /** true = feature à quota (consumable), false = accès binaire oui/non. */
    public function isConsumable(): bool
    {
        return match ($this) {
            self::MAX_EMPLOYEES,
            self::MAX_ADMINS => true,
            default          => false,
        };
    }

    /** Retourne toutes les features consumables. */
    public static function consumables(): array
    {
        return array_values(array_filter(self::cases(), fn ($f) => $f->isConsumable()));
    }

    /** Retourne toutes les features booléennes. */
    public static function booleans(): array
    {
        return array_values(array_filter(self::cases(), fn ($f) => ! $f->isConsumable()));
    }
}