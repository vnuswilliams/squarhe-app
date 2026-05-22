@props([
    'position' => 'bottom-start',
    'offset' => 3,
    'presets' => [],
    'trigger' => null,
    'label' => 'select a date',
    'variant' => 'default',
    'clearable' => false,
    'invalid' => false,

    // pass them through to the calendar core
    'mode' => 'single',
    'max' => null,
    'min' => null,
    'maxRange' => null,
    'minRange' => null,
    'readonly' => false,
    'allowNavigation' => true,
    'fixedWeeks' => 0,
    'weekNumbers' => false,
    'size' => 'md',
    'months' => 1,
    'unavailableDates' => [],
    'selectableMonths' => false,
    'selectableYears' => false,
    'withToday' => false,
    'locale' => 'auto',
    'startDay' => 'auto',
    'yearsRange' => [-10, +10],
    'openTo' => null,
    'forceOpenTo' => false,
    'topInputs' => false,
    'invalid' => false,

    // special days props
    'specialDays' => [],
    'specialDisabled' => [],
    'specialTooltips' => [],
    'separator' => '-',
    'rangeSeparator' => 'to',
])

@php
    // Process presets
    $showPresets = filled($presets);
    
    if (is_string($presets)) {
        $presets = explode(',', $presets);
        $presets = array_map('trim', $presets);
    } elseif (!is_array($presets)) {
        $presets = [];
    }

    $allPresets = \App\Enums\DateRangePreset::all();

    $activatedPresets = \App\Enums\DateRangePreset::forJs($presets);

    $invalid = $invalid ?? ($name && ($errors->has($name) || $errors->has("$name.*")));
@endphp

<div 
    x-data="datePickerComponent({
        allPresets: @js($allPresets),
        activatedPresets: @js($activatedPresets),
        mode: @js($mode),
        variant: @js($variant),
        rangeSeparator: @js($rangeSeparator)
    })"
    wire:ignore
>
    <div 
        class=""
    >
        <div 
            x-ref="popoverTrigger"
            {{ $attributes }}
        >
            @if($trigger && $variant !== 'pills')
                <div {{ $trigger->attributes }}>
                    {{ $trigger }}
                </div>
            @elseif(!($trigger instanceof \Illuminate\View\ComponentSlot) && $variant === "pills" && $mode === "multiple")
               <x-ui.date-picker.pills :$attributes :$invalid :$clearable/>
            @else
               <x-ui.date-picker.button :$attributes :$invalid :$clearable/>
            @endif
        </div>
        <x-ui.date-picker.dialog
            x-show="__open"
            x-on:click.away="hide"
            x-on:keydown.escape="hide"
            :attributes="(new \Illuminate\View\ComponentAttributeBag)->merge([
                'x-anchor.' . $position . '.offset.' . $offset => '$refs.popoverTrigger',
            ])"
        >
            <div class="flex dates-stretch">                  
                @if ($showPresets)
                    <x-ui.date-picker.presets :presets="$presets" />
                @endif

                <x-ui.calendar 
                    :$mode
                    :$max
                    :$min
                    :$maxRange
                    :$minRange
                    :$readonly
                    :$allowNavigation
                    :$fixedWeeks
                    :$weekNumbers
                    :$size
                    :$months
                    :$unavailableDates
                    :$selectableMonths
                    :$selectableYears
                    :$withToday
                    :$locale
                    :$startDay
                    :$yearsRange
                    :$openTo
                    :$forceOpenTo
                    :$specialDays
                    :$specialDisabled
                    :$specialTooltips
                    :$separator
                />
            </div>
        </x-ui.date-picker.dialog>
    </div>
</div>