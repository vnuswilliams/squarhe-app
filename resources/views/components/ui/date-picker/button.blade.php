@aware([
    'size' => 'size',
    'label' => 'select date',
])

@props([
    'invalid' => false,
    'clearable' => false
])

<button
    type="button"
    x-bind:data-open="__open"
    x-on:click="toggle()"
    {{  $attributes->class([ 
            "dark:bg-white/7 bg-white data-open:ring-2 border rounded-box flex items-center gap-1 [:where(&)]:w-48 shadow-xs disabled:shadow-none  dark:disabled:bg-white/6",
            'border-red-600/30 border-2 data-open:border-red-600/30 data-open:ring-red-600/20 dark:border-red-400/30 dark:data-open:border-red-400/30 dark:data-open:ring-red-400/20' => $invalid,
            'border-black/10 data-open:border-black/15 data-open:ring-neutral-900/15 dark:border-white/15 dark:data-open:border-white/20 dark:data-open:ring-neutral-100/15' => !$invalid,
            match($size){
                'sm' => 'min-h-8 p-0.5 text-sm gap-0.5',
                default => 'min-h-10 px-2 text-base gap-1'
            }
        ])
    }}
>
    <x-ui.icon name="calendar" variant="mini"/>
    
    <span 
        class="truncate"
        x-text="triggerLabel || @js($label)"
    ></span>
    {{-- HACK TO COMPENSATE LAYOUT SHIFT BY THE LABEL ABOCE --}}
    <span 
        x-show="false"
    >
        {{ $label }}
    </span>
    
    {{-- CLEAR --}}
    @if ($clearable)
        <div 
            x-show="selectedDates?.length"
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
</button>