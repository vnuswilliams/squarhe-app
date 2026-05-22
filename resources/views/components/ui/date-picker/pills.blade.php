@aware([
    'size' => 'size',
    'label' => 'select date',
])

@props([
    'invalid' => false,
    'clearable' => false
])

<div
    x-on:click="toggle()"
    x-bind:data-open="__open"
    {{
        $attributes->class([
            "dark:bg-white/5 bg-white data-open:ring-2 border rounded-box flex items-center gap-1 font-medium [:where(&)]:w-64",
            'border-red-600/30 border-2 data-open:border-red-600/30 data-open:ring-red-600/20 dark:border-red-400/30 dark:data-open:border-red-400/30 dark:data-open:ring-red-400/20' => $invalid,
            'border-black/10 data-open:border-black/15 data-open:ring-neutral-900/15 dark:border-white/15 dark:data-open:border-white/20 dark:data-open:ring-neutral-100/15' => !$invalid,
            match($size){
                'sm' => 'min-h-8 p-0.5 text-sm gap-0.5',
                default => 'min-h-10 p-1 text-base gap-1'
            }
        ])
     }}
> 
    <div class="flex items-center flex-wrap gap-1 flex-1" >
        <template 
            x-for="(date, index) in selectedDates" 
            x-bind:key="index"
        >
            <div
                @class([
                    "inline-flex items-center gap-1 rounded-md bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200 font-medium  shrink-0",
                    match($size) {
                        'sm' => ' p-0.75',
                        default => 'p-1'           
                    }
                ])
            >
                <span class="truncate" x-text="date.label"></span>

                <button
                    type="button"
                    class="rounded hover:bg-neutral-200 dark:hover:bg-neutral-600 p-0.25 opacity-60 hover:opacity-100 transition-opacity"
                    x-on:click.stop="removeDate(date.value)"
                    data-slot="remove-date"
                    x-bind:aria-label="'Remove ' + date.label"
                >
                    <x-ui.icon 
                        name="x-mark"
                        class="size-4 opacity-60"
                    />
                </button>
            </div>
        </template>
        
        <template x-if="!selectedDates?.length">
            <span class="pl-1.5">
                {{ $label }}
            </span>
        </template>
        {{-- a hack to compensate the timing required above for alpine to show it and hide this --}}
        <span x-show="false" class="pl-1.5">
            {{ $label }}
        </span>
    </div>

    {{-- CLEAR --}}
    @if ($clearable)
        <div 
            x-show="hasSelected"
            x-on:click="reset" 
            class="ml-auto self-start"
            x-cloak 
        >
            <x-ui.icon
                name="x-mark"
                @class([
                    "rounded-md dark:hover:bg-white/5 ml-auto hover:bg-neutral-800/5  ",
                    match($size){
                        'sm' => 'size-5 p-0.25',
                        default => 'size-8 p-1',
                    }      
                ]) 
            />
        </div>
    @endif
</div>