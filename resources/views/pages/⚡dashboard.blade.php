<?php

use App\Models\Invitation;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{

public function mount() {
    if(session()->has('error')):
        Flux::toast(variant:'danger', text: session('error'));
    elseif(session()->has('success')):
        Flux::toast(variant:'success', text: session('success'));
    endif;
}
    #[Computed()]
    public function invitations()
    {
         return Invitation::with(['sender', 'company'])
        ->where('recipient_id', auth()->id())
        ->where('expires_at', '>', now())
        ->latest()
        ->get();
      //  ->whereNull('accepted_at')
    }

    public function render()
    {
   return     $this->view()->title(__('Dashboard'));
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

    {{-- Reste du dashboard ici --}}
</div>