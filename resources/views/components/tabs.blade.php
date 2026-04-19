@props(['tabs' => [], 'storageKey' => 'activeTab'])

<div x-data="{
    activeTab: parseInt(localStorage.getItem('{{ $storageKey }}')) || 0,
    setActiveTab(index) {
        this.activeTab = index;
        localStorage.setItem('{{ $storageKey }}', index);
    }
}" x-init="if (activeTab >= {{ count($tabs) }}) activeTab = 0;">
    <nav class="overflow-x-auto flex gap-x-1 rounded-lg bg-gray-100 dark:bg-neutral-700 p-1" aria-label="Tabs"
        role="tablist" aria-orientation="horizontal">
        @foreach ($tabs as $title)
        <button type="button"
            @click="setActiveTab({{ $loop->index }})"
            :class="activeTab === {{ $loop->index }} ? 'bg-white text-gray-700 dark:bg-neutral-800 dark:text-white' : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-white'"
            class="cursor-pointer py-3 px-4 text-center basis-0 grow inline-flex justify-center items-center gap-x-2 text-sm font-medium focus:outline-hidden focus:text-gray-700 rounded-lg disabled:opacity-50 disabled:pointer-events-none dark:focus:text-white whitespace-nowrap min-w-0"
            id="tab-item-{{ $loop->index }}" :aria-selected="activeTab === {{ $loop->index }}"
            aria-controls="tab-panel-{{ $loop->index }}" role="tab">
            {{ $title }}
        </button>
        @endforeach
    </nav>

    <div class="mt-3">
        @foreach ($tabs as $title)
        @php
        $slotName = 'tab' . ($loop->index + 1);
        @endphp
        <div id="tab-panel-{{ $loop->index }}" x-show="activeTab === {{ $loop->index }}" x-transition role="tabpanel"
            :aria-labelledby="'tab-item-' + {{ $loop->index }}">
            {{ ${$slotName} ?? '' }}
        </div>
        @endforeach
    </div>
</div>