<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Métriques')] class extends Component {
};
?>

<div class="space-y-6">
    <div>
        <flux:heading level="1" class="font-bold">{{ __('Métriques') }}</flux:heading>
        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-300">
            {{ __('Gérez les données métriques de votre entreprise.') }}
        </flux:text>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:text>{{ __('Le module métriques sera bientôt disponible.') }}</flux:text>
    </div>
</div>
