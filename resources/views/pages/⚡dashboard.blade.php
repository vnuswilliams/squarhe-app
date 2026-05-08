<?php

use App\Models\Invitation;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public function mount()
    {
        if (session()->has('error')) {
            Flux::toast(variant: 'danger', text: session('error'));
        } elseif (session()->has('success')) {
            Flux::toast(variant: 'success', text: session('success'));
        }
    }

    #[Computed()]
    public function invitations()
    {
        return Invitation::with(['sender', 'company'])
            ->whereNull('accepted_at')
            ->where('recipient_id', auth()->id())
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }

    public function render()
    {
        return $this->view()->title(__('Dashboard'));
    }
};
?>

<div>
    @if($this->invitations->isNotEmpty())
        <div class="mb-8 space-y-3">
            <flux:heading size="sm" class="flex items-center gap-2">
                <flux:icon.envelope-open class="size-4 text-amber-500" />
                {{ __('Pending invitations') }}
                <flux:badge color="amber" size="sm">{{ $this->invitations->count() }}</flux:badge>
            </flux:heading>

            @foreach($this->invitations as $invitation)
                <div class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4
                            dark:border-amber-500/20 dark:bg-amber-500/5">

                    {{-- Barre colorée à gauche --}}
                    <span class="absolute inset-y-0 left-0 w-1 rounded-l-xl bg-amber-400 dark:bg-amber-500"></span>

                    <div class="flex items-start justify-between gap-4 pl-2">

                        {{-- Infos invitation --}}
                        <div class="space-y-1 min-w-0">
                            <flux:text class="font-medium text-zinc-800 dark:text-zinc-100">
                                <span class="text-amber-600 dark:text-amber-400">{{ $invitation->sender->name }}</span>
                                {{ __('has invited you to join') }}
                                <span class="font-semibold">{{ $invitation->company->name }}</span>
                            </flux:text>

                            <div class="flex flex-wrap items-center gap-3">
                                <flux:badge variant="pill" color="blue" size="sm">
                                    <flux:icon.shield-check class="size-3 mr-1" />
                                    {{ $invitation->role }}
                                </flux:badge>

                                <flux:text class="text-xs text-zinc-400 flex items-center gap-1">
                                    <flux:icon.clock class="size-3" />
                                    {{ __('Expires') }} {{ $invitation->expires_at->diffForHumans() }}
                                </flux:text>
                            </div>
                        </div>

                        {{-- Bouton accepter --}}
                        <a href="{{ route('invitation.accept', [
                                'company_code' => $invitation->company_code,
                                'invitation'   => $invitation->id,
                            ]) }}"
                            class="shrink-0"
                        >
                            <flux:button variant="primary" size="sm" icon="check">
                                {{ __('Accept') }}
                            </flux:button>
                        </a>

                    </div>
                </div>
            @endforeach
        </div>
    @endif


    <!-- Top Bar -->
    <div class="px-6 py-4 flex items-center justify-between flex-shrink-0">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Tableau de bord</h1>
        <p class="text-sm text-gray-500 mt-0.5">Vendredi 2 Mai 2025 — Cycle de paie : Mai 2025</p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Alerte CNPS -->
        <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 text-sm font-medium px-3 py-2 rounded-xl">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path></svg>
          Déclaration CNPS dans 5 jours
        </div>
        <!-- Notif -->
        <button class="relative w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
      </div>
    </div>

    <!-- Scrollable Body -->
    <x-delta-card  :cards="[
            [
                'label' => 'effecti total',
                'current' =>148,
                'delta' => '',
                'color' => 'blue'
            ],
            [
                'label' => 'Masse salariale',
                'current' =>  12.5,
                'delta' => '',
                'color' => 'emerald'
            ],
            [
                'label' => 'Congés en cours',
                'current' =>  12,
                'delta' => '',
                'color' => 'rose'
            ],
            [
                'label' => 'Contrat exprirant',
                'current' =>  12,
                'delta' => '',
                'color' => 'rose'
            ]
        ]" 
        />
        

       

</div>