<x-layouts::app.sidebar :title="$title ?? null">
    <div x-data x-show="$store.offline.showSyncBanner" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-full max-w-md px-4">
        <div class="bg-blue-600 text-white p-4 rounded-xl shadow-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <flux:icon.arrow-path class="animate-spin w-5 h-5" />
                <p class="text-sm font-medium">
                    {{ __('offline.sync_banner') }}
                </p>
            </div>
            <button @click="$store.offline.triggerSync()" class="text-xs bg-white text-blue-600 px-3 py-1.5 rounded-lg font-bold hover:bg-blue-50 transition-colors">
                {{ __('offline.sync_action') }}
            </button>
        </div>
    </div>

    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
