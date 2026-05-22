@props([
    'presets' => []
])

@php
   $displayPresets = \App\Enums\DateRangePreset::forJs(is_array($presets) ? $presets : []);
@endphp

<div
    x-ref="presetsContainer"
    x-init="
        $nextTick(() => {
            const calendar = $el.nextElementSibling;
            if (calendar) {
                const updateHeight = () => { $el.style.height = calendar.offsetHeight + 'px'; };
                updateHeight();
                new ResizeObserver(updateHeight).observe(calendar);
            }
        });
    "
    class="border-r w-36 dark:border-neutral-800 border-neutral-200 bg-white dark:bg-neutral-900 flex flex-col"
>
    <div class="px-1 flex flex-col h-full">
        <h3 class="px-3 py-2 text-sm gap-2 flex items-center font-semibold text-neutral-900 dark:text-neutral-100 mb-3 flex-shrink-0">
            <x-ui.icon name="calendar-date-range" class="w-4 h-4 flex-shrink-0" />
            Date Range
        </h3>

        <div
            class="space-y-1 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none] overflow-y-auto flex-1"
            x-on:keydown.up.prevent="$focus.previous()"
            x-on:keydown.down.prevent="$focus.next()"
        >
            @foreach ($displayPresets as $preset)
                <button
                    type="button"
                    x-on:click.stop="selectPreset('{{ $preset->key }}')"
                    x-bind:data-selected="isPresetActive('{{ $preset->key }}')"
                    class="text-neutral-700 focus:bg-(--color-primary)/3 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 data-selected:bg-(--color-primary)/5 data-selected:text-(--color-primary) font-medium w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-150"
                >
                    <span class="flex-1 text-left">{{ $preset->label }}</span>
                    <x-ui.icon
                        name="check"
                        class="w-4 h-4 flex-shrink-0 transition-opacity"
                        x-show="isPresetActive('{{ $preset->key }}')"
                    />
                </button>
            @endforeach
        </div>
    </div>
</div>