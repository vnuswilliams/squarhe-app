<?php

use App\Enums\PlanEnum;
use App\Models\Company;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Souscription')] class extends Component
{
    // ── État local ────────────────────────────────────────────────────────────
    public string $selectedFamily = 'starter';

    #[Validate('required|string')]
    public string $selectedPlan = '';

    // ─────────────────────────────────────────────────────────────────────────
    #[Computed]
    public function company()
    {
        return auth()->user()->company;
    }

    public function mount(): void
    {

        // Pré-sélectionner le plan actif si existant
        $current = app(SubscriptionService::class)->currentPlan($this->company);

        if ($current) {
            $this->selectedFamily = $current->family();
            $this->selectedPlan = $current->value;
        } else {
            // Par défaut : premier plan Starter
            $this->selectedFamily = 'starter';
            $this->selectedPlan = PlanEnum::STARTER_S1->value;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Computed properties (mises en cache par Livewire 4)
    // ─────────────────────────────────────────────────────────────────────────

    #[Computed]
    public function currentPlan(): ?PlanEnum
    {
        return app(SubscriptionService::class)->currentPlan($this->company);
    }

    #[Computed]
    public function hasActiveSubscription(): bool
    {
        return app(SubscriptionService::class)->hasActiveSubscription($this->company);
    }

    #[Computed]
    public function remainingSlots(): int
    {
        return app(SubscriptionService::class)->remainingEmployeeSlots($this->company);
    }

    #[Computed]
    public function expiresAt(): ?Carbon
    {
        return app(SubscriptionService::class)->expiresAt($this->company);
    }

    /**
     * Plans groupés par famille pour la vue.
     * Chaque item : ['enum' => PlanEnum, 'isCurrent' => bool]
     *
     * @return array<string, list<array{enum: PlanEnum, isCurrent: bool}>>
     */
    #[Computed]
    public function groupedPlans(): array
    {
        $current = $this->currentPlan;
        $result = [];

        foreach (PlanEnum::paidFamilies() as $family => $cases) {
            foreach ($cases as $case) {
                $result[$family][] = [
                    'enum' => $case,
                    'isCurrent' => $current?->value === $case->value,
                ];
            }
        }

        return $result;
    }

    /** PlanEnum actuellement mis en avant dans la vue (peut être null si saisie invalide). */
    #[Computed]
    public function previewPlan(): ?PlanEnum
    {
        return PlanEnum::tryFrom($this->selectedPlan);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Actions
    // ─────────────────────────────────────────────────────────────────────────

    /** Appelé quand l'utilisateur clique sur une card. */
    public function selectPlan(string $value): void
    {
        $this->selectedPlan = $value;
    }

    /** Change la famille de plans affichée (tabs). */
    public function selectFamily(string $family): void
    {
        $this->selectedFamily = $family;

        // Auto-sélectionner le premier plan de la nouvelle famille
        $cases = match ($family) {
            'starter' => PlanEnum::starterCases(),
            'growth' => PlanEnum::growthCases(),
            'business' => PlanEnum::businessCases(),
            default => [],
        };

        if (! empty($cases)) {
            $this->selectedPlan = $cases[0]->value;
        }
    }

    /** Souscrit ou change de plan. */
    public function subscribe(): void
    {
        // Policy check
        // $this->authorize('subscribe', $this->company);

        $this->validate();

        $planEnum = PlanEnum::tryFrom($this->selectedPlan);

        if (! $planEnum) {
            Flux::toast(variant: 'warning', text: __('toast.subscription.planInvalid'));

            return;
        }

        if ($planEnum->value === $this->currentPlan?->value) {
            Flux::toast(variant: 'warning', text: __('toast.subscription.currentPlan'));

            return;
        }
        try {
            app(SubscriptionService::class)->subscribeTo($this->company, $planEnum);
            unset($this->currentPlan, $this->hasActiveSubscription, $this->remainingSlots, $this->expiresAt);

            // Invalider les computed properties mises en cache
            Flux::toast(variant: 'success', text: __('toast.subscription.success', ['plan' => "{$planEnum->label()} ({$planEnum->tranche()}), {$planEnum->maxEmployees()}  employés max."]));

        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: __('toast.subscription.fail'));
            report($e);
        }
    }

    /** Annule l'abonnement actif. */
    public function cancelSubscription(): void
    {

        // Gate::authorize('cancelSubscription', [Subscription::class] );
        try {
            app(SubscriptionService::class)->cancel($this->company);

            unset($this->currentPlan, $this->hasActiveSubscription, $this->remainingSlots, $this->expiresAt);

            Flux::toast(variant: 'success', text: __('toast.subscription.cancelSuccess'));

        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: __('toast.subscription.cancelFail'));
            report($e);
        }
    }

    /** Supprimer l'abonnement actif. */
    public function deleteSubscription(): void
    {

        try {
            app(SubscriptionService::class)->suppress($this->company);

            unset($this->currentPlan, $this->hasActiveSubscription, $this->remainingSlots, $this->expiresAt);

            Flux::toast(variant: 'success', text: __('toast.subscription.deleteSuccess'));

        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: __('toast.subscription.deleteFail'));
            report($e);
        }
    }
};

?>

<section class="w-full">
<div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <flux:heading size="xl"> Choisissez votre offre </flux:heading>
            <flux:text variant="subtle">
            Tarif fixe mensuel · Changez de tranche à tout moment · 7 jours de grâce inclus
            </flux:text>
        </div>
       
    </div>

        <div class="max-w-5xl mx-auto my-6 space-y-8 ">
          

            {{-- ════════════════════════════════════════════════════════
                BANDEAU PLAN ACTUEL
            ════════════════════════════════════════════════════════ --}}
            @if ($this->currentPlan)
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-zinc-700/60 bg-zinc-900 px-4 py-3">
                    <flux:badge color="orange" size="sm" class="shrink-0">Plan actuel</flux:badge>
                    <span class="text-sm text-zinc-200 font-medium">
                        {{ $this->currentPlan->label() }}
                        <span class="text-zinc-500">·</span>
                        Tranche {{ $this->currentPlan->tranche() }}
                        <span class="text-zinc-500">·</span>
                        {{ $this->currentPlan->maxEmployees() }} employés max
                    </span>

                    @if ($this->expiresAt)
                        <span class="text-xs text-zinc-500 ml-auto">
                            Expire le {{ $this->expiresAt->format('d/m/Y') }}
                        </span>
                    @endif

                    <div class="flex items-center gap-1.5 text-xs text-zinc-400
                        @if ($this->remainingSlots <= 0) text-red-400 @elseif ($this->remainingSlots <= 3) text-amber-400 @endif">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                        {{ $this->remainingSlots }} slot(s) restant(s)
                    </div>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════
                TABS FAMILLE
            ════════════════════════════════════════════════════════ --}}
            <div class="flex gap-2 flex-wrap">
                @php
                    $families = [
                        'starter'  => ['label' => '🚀 Starter',    'color' => 'sky'],
                        'growth'   => ['label' => '⭐ Croissance',  'color' => 'green'],
                        'business' => ['label' => '🏢 Business',   'color' => 'violet'],
                    ];
                @endphp

                @foreach ($families as $key => $meta)
                    <button
                        wire:click="selectFamily('{{ $key }}')"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition-all duration-150
                            @if ($selectedFamily === $key)
                                @if ($key === 'starter')  border-sky-500 text-sky-400 bg-sky-500/10
                                @elseif ($key === 'growth') border-emerald-500 text-emerald-400 bg-emerald-500/10
                                @else border-violet-500 text-violet-400 bg-violet-500/10
                                @endif
                            @else
                                border-zinc-700 text-zinc-400 hover:border-zinc-500 hover:text-zinc-200
                            @endif"
                    >
                        {{ $meta['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- ════════════════════════════════════════════════════════
                GRILLE DE PLANS
            ════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach ($this->groupedPlans[$selectedFamily] as $item)
                    @php
                        /** @var \App\Enums\PlanEnum $plan */
                        $plan      = $item['enum'];
                        $isCurrent = $item['isCurrent'];
                        $isSelected = $selectedPlan === $plan->value;

                        $ringClass = match ($selectedFamily) {
                            'starter'  => 'ring-sky-500 border-sky-500',
                            'growth'   => 'ring-emerald-500 border-emerald-500',
                            'business' => 'ring-violet-500 border-violet-500',
                            default    => 'ring-zinc-500 border-zinc-500',
                        };

                        $badgeColor = match ($selectedFamily) {
                            'starter'  => 'sky',
                            'growth'   => 'green',
                            'business' => 'violet',
                            default    => 'zinc',
                        };
                    @endphp

                    <button
                        wire:click="selectPlan('{{ $plan->value }}')"
                        type="button"
                        class="relative flex flex-col gap-2 rounded-xl border bg-zinc-900 p-4 text-left
                            transition-all duration-150 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none
                            @if ($isSelected)
                                {{ $ringClass }} ring-2 shadow-lg
                            @else
                                border-zinc-700/60 hover:border-zinc-600
                            @endif"
                    >
                        {{-- Badge position absolue --}}
                        <div class="absolute top-3 right-3">
                            @if ($isCurrent)
                                <flux:badge color="orange" size="sm">Actuel</flux:badge>
                            @else
                                <flux:badge color="{{ $badgeColor }}" size="sm">{{ $plan->tranche() }}</flux:badge>
                            @endif
                        </div>

                        <span class="text-xl font-black text-white leading-none">
                            {{ $plan->tranche() }}
                        </span>

                        <span class="text-xs text-zinc-500">
                            {{ $plan->maxEmployees() }} employés max
                        </span>

                        <div class="mt-1 pt-2 border-t border-zinc-800">
                            <span class="text-lg font-bold text-white">
                                {{ number_format($plan->monthlyPrice(), 0, ',', ' ') }}
                            </span>
                            <span class="text-xs text-zinc-500"> FCFA/mois</span>
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- ════════════════════════════════════════════════════════
                RÉCAPITULATIF & CTA
            ════════════════════════════════════════════════════════ --}}
            @if ($this->previewPlan)
                @php $preview = $this->previewPlan; @endphp

                <div class="rounded-2xl border border-zinc-700/60 bg-zinc-900 p-6
                            flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">

                    {{-- Infos plan sélectionné --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-lg font-black text-white">
                                Offre {{ $preview->label() }} · {{ $preview->tranche() }}
                            </span>
                            @if ($this->currentPlan?->value === $preview->value)
                                <flux:badge color="orange" size="sm">Plan actif</flux:badge>
                            @elseif ($this->currentPlan)
                                <flux:badge color="zinc" size="sm">Changement</flux:badge>
                            @else
                                <flux:badge color="green" size="sm">Nouveau</flux:badge>
                            @endif
                        </div>

                        <div class="text-sm text-zinc-400 space-y-0.5">
                            <p>{{ $preview->maxEmployees() }} employés maximum</p>
                            <p>Facturation mensuelle · 7 jours de grâce après expiration</p>
                            @if ($preview->graceDays() > 0)
                                <p class="text-xs text-zinc-600">
                                    Les changements de plan prennent effet immédiatement.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Prix + bouton --}}
                    <div class="flex flex-col items-end gap-3 shrink-0 w-full sm:w-auto">
                        <div class="text-right">
                            <span class="text-4xl font-black text-white tracking-tight">
                                {{ number_format($preview->monthlyPrice(), 0, ',', ' ') }}
                            </span>
                            <span class="text-zinc-400 text-sm"> FCFA</span>
                            <p class="text-xs text-zinc-600">par mois · HT</p>
                        </div>

                        <flux:button
                            wire:click="subscribe"
                            wire:loading.attr="disabled"
                            variant="primary"
                            class="w-full sm:w-auto !bg-orange-500 hover:!bg-orange-400 !text-black !font-bold"
                            :disabled="$this->currentPlan?->value === $preview->value"
                        >
                            <span wire:target="subscribe">
                                @if ($this->currentPlan?->value === $preview->value)
                                    Plan déjà actif
                                @elseif ($this->currentPlan)
                                    Changer pour ce plan
                                @else
                                    Souscrire à ce plan
                                @endif
                            </span>
                           
                        </flux:button>
                    </div>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════
                COMPARATIF RAPIDE DES 3 OFFRES
            ════════════════════════════════════════════════════════ --}}
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900/50 p-6 space-y-4">
                <flux:heading size="sm" class="text-zinc-300">Comparatif rapide</flux:heading>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    {{-- Starter --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <span class="font-bold text-sky-400">Starter</span>
                        </div>
                        <ul class="text-zinc-400 space-y-1 pl-4">
                            <li>5 à 20 employés</li>
                            <li>14 900 → 22 400 FCFA/mois</li>
                            <li>Gestion de la paie</li>
                        </ul>
                    </div>

                    {{-- Croissance --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <span class="font-bold text-emerald-400">Croissance ⭐</span>
                        </div>
                        <ul class="text-zinc-400 space-y-1 pl-4">
                            <li>20 à 50 employés</li>
                            <li>34 900 → 55 900 FCFA/mois</li>
                            <li>Paie + RH structurée</li>
                        </ul>
                    </div>

                    {{-- Business --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500"></div>
                            <span class="font-bold text-violet-400">Business</span>
                        </div>
                        <ul class="text-zinc-400 space-y-1 pl-4">
                            <li>50 à 150 employés</li>
                            <li>64 900 → 124 900 FCFA/mois</li>
                            <li>Pilotage PME + intégrations</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════
                ANNULATION
            ════════════════════════════════════════════════════════ --}}
            @if ($this->currentPlan && $this->currentPlan !== \App\Enums\PlanEnum::FREE)
                    <div class="text-center">
                        <flux:button
                            wire:click="cancelSubscription"
                            wire:confirm="Annuler votre abonnement ? Vous conservez l'accès jusqu'à expiration."
                            wire:loading.attr="disabled"
                        >
                            Annuler mon abonnement
                        </flux:button>
                        <flux:button
                        variant="danger"
                            wire:click="deleteSubscription"
                            wire:confirm="Supprimer votre abonnement ? Vous ne conservez pas l'accès jusqu'à expiration."
                            wire:loading.attr="disabled"
                        >
                            Supprimer mon abonnement
                        </flux:button>
                    </div>
                    <flux:callout icon="information-circle" class="mt-5">
                        <flux:callout.heading> Information</flux:callout.heading>
                        <flux:callout.text>
                            L'annulation de votre abonnement vous laisse la possibilité de continuer à utiliser les fonctionnalités 
                            jusqu'a la fin de votre abonnement hors la suppression supprime définitive votre abonnement et vous perdrez accès à toutes les fonctionnalités.
                        </flux:callout.text>
                    </flux:callout>
                    @endif
        </div>

</section>
