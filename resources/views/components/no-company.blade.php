    <x-container class="card bg-neutral-100 text-neutral-800   dark:bg-zinc-900 dark:border-zinc-700 dark:text-neutral-100">

        <flux:heading level="1" size="xl" class="flex items-center gap-2 font-semibold">
            <flux:icon.information-circle />
            {{ __('how.create.society') }}
        </flux:heading>
        <div class="flex flex-wrap justify-center gap-6 my-4 mx-4 md:justify-between md:mx-6">
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-zinc-600 p-2 flex items-center justify-center">1</span>
                <div class="flex flex-col">
                    <span>
                        {{ __('click.on.create.company') }}
                    </span>
                    <span class="text-zinc-400">{{ __('access.the.creation.form') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-zinc-600 p-2 flex items-center justify-center">2</span>
                <div class="flex flex-col">
                    <span>
                        {{ __('fill.in.the.information') }}
                    </span>
                    <span class="text-zinc-400">{{ __('fill.in.your.company.info') }}</span>
                </div>
            </div>
            {{-- <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-zinc-600 p-2 flex items-center justify-center">3</span>
                <div class="flex flex-col">
                    <span>
                        Cliquez sur le button Créer une societe
                    </span>
                    <span class="text-zinc-400">remplissez les infos de votre compagnie</span>
                </div>
            </div> --}}
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-indigo-600 p-2 flex items-center justify-center">
                    <flux:icon.sparkles />
                </span>
                <div class="flex flex-col">
                    <span>
                        {{ __('and.voila.register.your.employees') }}
                    </span>
                    <span class="text-zinc-400 ">{{ __('register.and.manage.your.employees') }}</span>
                </div>
            </div>
        </div>
        <flux:button href="{{ route('settings.company.add') }}" variant="primary">
            {{ __('add.company') }}
        </flux:button>
    </x-container>
    {{-- <x-container class="card bg-neutral-100 text-neutral-800   dark:bg-zinc-900 dark:border-zinc-700 dark:text-neutral-100">
        <div class="flex items-center justify-between">

            <div class="">
                <flux:heading size="lg" class="font-semibold">{{__('company.infos')}}</flux:heading>
    <flux:text class="text-sm text-gray-400">
        {{ __('modify.company') }}
    </flux:text>
    </div>
    <flux:button href="{{route('settings.update.company')}}">{{ __('edit.company') }}</flux:button>
    </div>
    <!-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius -->
    </x-container> --}}