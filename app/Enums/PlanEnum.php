<?php

namespace App\Enums;

use Squarhe\Subscription\Enums\PeriodicityType;

enum PlanEnum: string
{
    // ── Gratuit ──────────────────────────────────────────────────────────────
    case FREE = 'free';

    // ── Starter ───────────────────────────────────────────────────────────────
    case STARTER_S1 = 'starter-s1';
    case STARTER_S2 = 'starter-s2';
    case STARTER_S3 = 'starter-s3';
    case STARTER_S4 = 'starter-s4';

    // ── Croissance ────────────────────────────────────────────────────────────
    case GROWTH_C1 = 'growth-c1';
    case GROWTH_C2 = 'growth-c2';
    case GROWTH_C3 = 'growth-c3';
    case GROWTH_C4 = 'growth-c4';
    case GROWTH_C5 = 'growth-c5';
    case GROWTH_C6 = 'growth-c6';
    case GROWTH_C7 = 'growth-c7';

    // ── Business ──────────────────────────────────────────────────────────────
    case BUSINESS_B1 = 'business-b1';
    case BUSINESS_B2 = 'business-b2';
    case BUSINESS_B3 = 'business-b3';
    case BUSINESS_B4 = 'business-b4';
    case BUSINESS_B5 = 'business-b5';

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : libellé affiché
    // ─────────────────────────────────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Gratuit',
            self::STARTER_S1,
            self::STARTER_S2,
            self::STARTER_S3,
            self::STARTER_S4 => 'Starter',
            self::GROWTH_C1,
            self::GROWTH_C2,
            self::GROWTH_C3,
            self::GROWTH_C4,
            self::GROWTH_C5,
            self::GROWTH_C6,
            self::GROWTH_C7 => 'Croissance',
            self::BUSINESS_B1,
            self::BUSINESS_B2,
            self::BUSINESS_B3,
            self::BUSINESS_B4,
            self::BUSINESS_B5 => 'Business',
        };
    }

    /** Code de tranche affiché (ex. S1, C3, B2). */
    public function tranche(): string
    {
        return match ($this) {
            self::FREE => '—',
            self::STARTER_S1 => 'S1',
            self::STARTER_S2 => 'S2',
            self::STARTER_S3 => 'S3',
            self::STARTER_S4 => 'S4',
            self::GROWTH_C1 => 'C1',
            self::GROWTH_C2 => 'C2',
            self::GROWTH_C3 => 'C3',
            self::GROWTH_C4 => 'C4',
            self::GROWTH_C5 => 'C5',
            self::GROWTH_C6 => 'C6',
            self::GROWTH_C7 => 'C7',
            self::BUSINESS_B1 => 'B1',
            self::BUSINESS_B2 => 'B2',
            self::BUSINESS_B3 => 'B3',
            self::BUSINESS_B4 => 'B4',
            self::BUSINESS_B5 => 'B5',
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : nombre maximal d'employés (= charges de la feature)
    // ─────────────────────────────────────────────────────────────────────────

    public function maxEmployees(): int
    {
        return match ($this) {
            self::FREE => 3,
            self::STARTER_S1 => 5,
            self::STARTER_S2 => 10,
            self::STARTER_S3 => 15,
            self::STARTER_S4 => 20,
            self::GROWTH_C1 => 20,
            self::GROWTH_C2 => 25,
            self::GROWTH_C3 => 30,
            self::GROWTH_C4 => 35,
            self::GROWTH_C5 => 40,
            self::GROWTH_C6 => 45,
            self::GROWTH_C7 => 50,
            self::BUSINESS_B1 => 50,
            self::BUSINESS_B2 => 60,
            self::BUSINESS_B3 => 75,
            self::BUSINESS_B4 => 100,
            self::BUSINESS_B5 => 150,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : nombre maximal d'admins (= charges de la feature)
    // ─────────────────────────────────────────────────────────────────────────

    public function maxAdmins(): int
    {
        return match (true) {
            $this === self::FREE => 1,
            in_array($this, self::starterCases(), true) => 1,
            in_array($this, self::growthCases(), true) => 2,
            in_array($this, self::businessCases(), true) => 5,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : features booléennes disponibles par famille
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne les features booléennes (non consumables) à attacher à ce plan.
     * Les features absentes du tableau ne sont tout simplement pas attachées
     * → le plan n'y a pas accès (comportement soulbscription natif).
     *
     * @return list<FeatureEnum>
     */
    public function booleanFeatures(): array
    {
        return match ($this->family()) {
            'free', 'starter' => [
                // Aucune feature booléenne sur FREE et Starter
            ],
            'growth' => [
                FeatureEnum::ESPACE_COLLABORATEUR,
                FeatureEnum::DOCUMENTS,
                FeatureEnum::RAPPORTS_AVANCES,
            ],
            'business' => [
                FeatureEnum::ESPACE_COLLABORATEUR,
                FeatureEnum::DOCUMENTS,
                FeatureEnum::RAPPORTS_AVANCES,
                FeatureEnum::SUPPORT_PRIORITAIRE,
            ],
            default => [],
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : prix mensuel en FCFA (0 = gratuit)
    // ─────────────────────────────────────────────────────────────────────────

    public function monthlyPrice(): int
    {
        return match ($this) {
            self::FREE => 0,
            self::STARTER_S1 => 14_900,
            self::STARTER_S2 => 17_400,
            self::STARTER_S3 => 19_900,
            self::STARTER_S4 => 22_400,
            self::GROWTH_C1 => 34_900,
            self::GROWTH_C2 => 38_400,
            self::GROWTH_C3 => 41_900,
            self::GROWTH_C4 => 45_400,
            self::GROWTH_C5 => 48_900,
            self::GROWTH_C6 => 52_400,
            self::GROWTH_C7 => 55_900,
            self::BUSINESS_B1 => 64_900,
            self::BUSINESS_B2 => 74_900,
            self::BUSINESS_B3 => 84_900,
            self::BUSINESS_B4 => 99_900,
            self::BUSINESS_B5 => 124_900,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Méta : périodicité soulbscription
    // ─────────────────────────────────────────────────────────────────────────

    public function periodicityType(): ?string
    {
        return match ($this) {
            self::FREE => null,            // plan permanent
            default => PeriodicityType::Month,
        };
    }

    public function periodicity(): ?int
    {
        return match ($this) {
            self::FREE => null,
            default => 1,
        };
    }

    /** Durée d'essai en jours (uniquement pour le plan gratuit). */
    public function trialDays(): ?int
    {
        return match ($this) {
            self::FREE => 15,
            default => null,
        };
    }

    /** Jours de grâce après expiration (0 pour le gratuit). */
    public function graceDays(): int
    {
        return match ($this) {
            self::FREE => 0,
            default => 7,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers de regroupement
    // ─────────────────────────────────────────────────────────────────────────

    public function family(): string
    {
        return match (true) {
            $this === self::FREE => 'free',
            in_array($this, self::starterCases(), true) => 'starter',
            in_array($this, self::growthCases(), true) => 'growth',
            in_array($this, self::businessCases(), true) => 'business',
        };
    }

    /** @return self[] */
    public static function freeCases(): array
    {
        return [self::FREE];
    }

    /** @return self[] */
    public static function starterCases(): array
    {
        return [self::STARTER_S1, self::STARTER_S2, self::STARTER_S3, self::STARTER_S4];
    }

    /** @return self[] */
    public static function growthCases(): array
    {
        return [self::GROWTH_C1, self::GROWTH_C2, self::GROWTH_C3, self::GROWTH_C4, self::GROWTH_C5, self::GROWTH_C6, self::GROWTH_C7];
    }

    /** @return self[] */
    public static function businessCases(): array
    {
        return [self::BUSINESS_B1, self::BUSINESS_B2, self::BUSINESS_B3, self::BUSINESS_B4, self::BUSINESS_B5];
    }

    /** @return self[] Plans payants uniquement, groupés par famille. */
    public static function paidFamilies(): array
    {
        return [
            'free' => self::freeCases(),
            'starter' => self::starterCases(),
            'growth' => self::growthCases(),
            'business' => self::businessCases(),
        ];
    }
}
