    <x-container class="card bg-neutral-100 text-neutral-800   dark:bg-zinc-900 dark:border-zinc-700 dark:text-neutral-100">

        <flux:heading level="1" size="xl" class="flex items-center gap-2 font-semibold">
            <flux:icon.information-circle />
            {{ __('nocompany.createcompany') }}
        </flux:heading>
        <div class="flex flex-nowrap justify-center gap-6 my-4 mx-4 md:justify-between md:mx-6">
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-zinc-600 p-2 flex items-center justify-center">1</span>
                <div class="flex flex-col">
                    <span>
                        {{ __('nocompany.create') }}
                    </span>
                    <span class="text-zinc-400">{{ __('nocompany.form') }}</span>
                </div>
            </div>
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-zinc-600 p-2 flex items-center justify-center">2</span>
                <div class="flex flex-col">
                    <span>
                        {{ __('nocompany.fill') }}
                    </span>
                    <span class="text-zinc-400">{{ __('nocompany.fillcompany') }}</span>
                </div>
            </div>
           
            <div class="flex items-center gap-4 ">
                <span class="rounded-full w-6 h-6 bg-indigo-600 p-2 flex items-center justify-center">
                    <flux:icon.sparkles />
                </span>
                <div class="flex flex-col">
                    <span>
                        {{ __('nocompany.voila') }}
                    </span>
                    <span class="text-zinc-400 ">{{ __('nocompany.register') }}</span>
                </div>
            </div>
        </div>
        <flux:button href="{{ route('settings.company.add') }}" variant="primary" wire:navigate>
            {{ __('nocompany.addbutton') }}
        </flux:button>
    </x-container>
  