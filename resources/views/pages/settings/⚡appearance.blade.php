<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium">{{ __('settings.appearance.language') }}</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('settings.appearance.select_language') }}</p>
            <div x-data="{ lang: '{{ app()->getLocale() }}' }" class="mt-2">
                <flux:radio.group variant="segmented" x-model="lang" @change="window.location.href = '/language/' + lang">
                    <flux:radio value="en">{{ __('settings.appearance.english') }}</flux:radio>
                    <flux:radio value="fr">{{ __('settings.appearance.french') }}</flux:radio>
                </flux:radio.group>
            </div>
        </div>
    </x-pages::settings.layout>
</section>