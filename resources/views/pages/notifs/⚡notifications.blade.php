<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

new #[Title('Notifications')] class extends Component
{
    public int $displayCount = 5;
    public int $activityDisplayCount = 5;

    public function loadMore(): void
    {
        $this->displayCount += 5;
    }

    public function loadMoreActivity(): void
    {
        $this->activityDisplayCount += 5;
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        Flux::toast(text: __('Notification marquée comme lue.'));
    }

    public function markAllAsRead(): void
    {
        foreach (Auth::user()->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        Flux::toast(text: __('Toutes les notifications ont été marquées comme lues.'));
    }

    #[Computed]
    public function notifications(): \Illuminate\Database\Eloquent\Collection
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->limit($this->displayCount)
            ->get();
    }

    #[Computed]
    public function totalNotificationsCount(): int
    {
        return Auth::user()->notifications()->count();
    }

    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function hasMoreNotifications(): bool
    {
        return $this->displayCount < $this->totalNotificationsCount;
    }

    #[Computed]
    public function activities(): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::query()
            ->where('causer_id', Auth::id())
            ->latest()
            ->limit($this->activityDisplayCount)
            ->get();
    }

    #[Computed]
    public function totalActivitiesCount(): int
    {
        return Activity::query()
            ->where('causer_id', Auth::id())
            ->count();
    }

    #[Computed]
    public function hasMoreActivities(): bool
    {
        return $this->activityDisplayCount < $this->totalActivitiesCount;
    }

    #[Computed]
    public function isOwner(): bool
    {
        return Auth::user()->hasRole('Owner');
    }
};
?>

<section class="w-full px-4 py-6 sm:px-6">
    <div class="mx-auto flex max-w-5xl flex-col gap-4">
        <div class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 bg-white/90 p-5 shadow-sm ring-1 ring-slate-900/5 dark:border-slate-800/80 dark:bg-slate-950/70 dark:ring-slate-300/5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <flux:heading size="xl">{{ __('Notifications') }}</flux:heading>
                        @if ($this->unreadNotificationsCount > 0)
                        <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                            {{ __(':count non lu(s)', ['count' => $this->unreadNotificationsCount]) }}
                        </span>
                        @else
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ __('Aucune notification non lue') }}
                        </span>
                        @endif
                    </div>

                    <flux:text class="max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                        {{ __('Retrouvez ici les dernières activités de votre compte et marquez-les comme lues sans perdre le fil.') }}
                    </flux:text>
                </div>

                <div class="grid gap-2 sm:flex sm:items-center">
                    <flux:button variant="ghost" wire:click="markAllAsRead" class="w-full sm:w-auto">
                        {{ __('Tout marquer comme lu') }}
                    </flux:button>
                </div>
            </div>
        </div>

        @php
        $tabs = [__('Notifications')];
        if ($this->isOwner) {
        $tabs[] = __('Logs d\'activité');
        }
        @endphp

        <x-tabs :tabs="$tabs" storageKey="notificationsTabs">
            @slot('tab1')
            <div class="grid gap-4">
                @forelse ($this->notifications as $notification)
                <article class="rounded-3xl border p-4 shadow-sm transition duration-150 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-950/80 {{ $notification->read_at ? 'border-slate-200/80 bg-white/90 dark:border-slate-800/80 dark:bg-slate-950/70' : 'border-emerald-400/30 bg-emerald-50/70 ring-1 ring-emerald-200/60 dark:bg-emerald-500/10' }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1 space-y-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <flux:heading size="base" class="font-semibold text-slate-900 dark:text-white">
                                    {{ $notification->data['company_name'] ?? __('Notification') }}
                                </flux:heading>

                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium uppercase tracking-[0.24em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <flux:text class="text-sm leading-6 text-slate-700 dark:text-slate-300">
                                @php
                                $action = $notification->data['action'] ?? null;
                                $modelName = $notification->data['model_name'] ?? null;
                                $modelDisplayName = $notification->data['model_display_name'] ?? null;
                                $userName = $notification->data['user_name'] ?? __('Un utilisateur');
                                $company = $notification->data['company_name'] ?? __('Entreprise inconnue');
                                @endphp
                                @if ($modelName === 'Employee')
                                @if ($action === 'created')
                                {{ __(':user a créé l\'employé :name', ['user' => $userName, 'name' => $modelDisplayName]) }}
                                @elseif ($action === 'updated')
                                {{ __(':user a modifié l\'employé :name', ['user' => $userName, 'name' => $modelDisplayName]) }}
                                @elseif ($action === 'deleted')
                                {{ __(':user a supprimé l\'employé :name', ['user' => $userName, 'name' => $modelDisplayName]) }}
                                @endif
                                @elseif ($modelName === 'Leave')
                                @if ($action === 'created')
                                {{ __(':user a ajouté un congé :type', ['user' => $userName, 'type' => $modelDisplayName]) }}
                                @elseif ($action === 'updated')
                                {{ __(':user a modifié un congé :type', ['user' => $userName, 'type' => $modelDisplayName]) }}
                                @elseif ($action === 'deleted')
                                {{ __(':user a supprimé un congé :type', ['user' => $userName, 'type' => $modelDisplayName]) }}
                                @endif
                                @else
                                {{ __('Vous avez une nouvelle notification concernant :company.', ['company' => $company]) }}
                                @endif
                            </flux:text>
                        </div>

                        <div class="flex flex-col gap-2 sm:items-end sm:justify-between">
                            @if (! $notification->read_at)
                            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300">
                                {{ __('Non lu') }}
                            </span>
                            @endif

                            <flux:button wire:click.prevent="markAsRead('{{ $notification->id }}')">
                                {{ __('Marquer comme lu') }}
                            </flux:button>
                        </div>
                    </div>
                </article>
                @empty
                <div class="rounded-3xl border border-dashed border-slate-300/80 bg-slate-50/80 p-6 text-center dark:border-slate-700 dark:bg-slate-950/60">
                    <flux:heading size="lg">{{ __('Aucune notification trouvée') }}</flux:heading>
                    <flux:text class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        {{ __('Vous êtes à jour. Revenez plus tard pour voir les nouvelles notifications.') }}
                    </flux:text>
                </div>
                @endforelse
            </div>

            @if ($this->hasMoreNotifications())
            <div class="flex justify-center">
                <flux:button variant="ghost" wire:click="loadMore">
                    {{ __('Voir plus de notifications') }}
                </flux:button>
            </div>
            @endif
            @endslot

            @if ($this->isOwner)
            @slot('tab2')
            <div class="grid gap-4">
                @forelse ($this->activities as $activity)
                <article class="rounded-3xl border border-slate-200/80 bg-white/90 p-4 shadow-sm transition duration-150 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-950/80">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1 space-y-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <flux:heading size="base" class="font-semibold text-slate-900 dark:text-white">
                                    {{ ucfirst($activity->log_name) }}
                                </flux:heading>

                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-medium uppercase tracking-[0.24em] text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <flux:text class="text-sm leading-6 text-slate-700 dark:text-slate-300">
                                <strong>{{ $activity->causer?->name ?? __('Utilisateur') }}</strong> - {{ $activity->description }}
                            </flux:text>

                            @if ($activity->properties)
                            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                <p><strong>{{ __('Propriétés') }}:</strong></p>
                                @foreach ($activity->properties as $key => $value)
                                <span class="inline-block mr-2">
                                    <strong>{{ str_replace('_', ' ', ucfirst($key)) }}:</strong>
                                    {{ is_array($value) ? json_encode($value) : $value }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($activity->event) }}
                            </span>
                        </div>
                    </div>
                </article>
                @empty
                <div class="rounded-3xl border border-dashed border-slate-300/80 bg-slate-50/80 p-6 text-center dark:border-slate-700 dark:bg-slate-950/60">
                    <flux:heading size="lg">{{ __('Aucun log d\'activité trouvé') }}</flux:heading>
                    <flux:text class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        {{ __('Les logs d\'activités apparaîtront ici.') }}
                    </flux:text>
                </div>
                @endforelse
            </div>

            @if ($this->hasMoreActivities())
            <div class="flex justify-center">
                <flux:button variant="ghost" wire:click="loadMoreActivity">
                    {{ __('Voir plus de logs') }}
                </flux:button>
            </div>
            @endif
            @endslot
            @endif
        </x-tabs>
    </div>
</section>