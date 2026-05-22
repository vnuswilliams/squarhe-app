{{-- don't be confused those refs are tied to date picker data stack and not the calendar dataStack, so while they share the same refs names there 
is no conflict due how alpine handle refs scopes --}}
<x-ui.input 
    x-ref="date_input_start"
    bindScopeToParent
    class="[&_input]:cursor-default"
    name="datePickerInput"
    inputmode="numeric"
    data-start
>
    <x-slot:right-icon>
        <x-ui.icon
            class="rounded-field hover:bg-white/5 size-full p-1"
            role="button"
            variant="mini"
            name="calendar"
            as="button"
            x-on:click="toggle()"
        />
    </x-slot:right-icon>        
</x-ui.input>