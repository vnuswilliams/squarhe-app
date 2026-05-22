{{-- this panel part, in next few weeks, going to be refactored to use @locus primitive which going to leverage the power on `popover` and `dialog` native html elements to be in the top layer in the brower, which going to solve the **parent clipping and overflows problems**   --}}

<div
    {{ $attributes->class("absolute [transform:z(0)] backdrop-blur-sm w-max my-4 bg-white [:where(&)]:w-full dark:bg-neutral-900 mt-1 border dark:border-neutral-800 border-neutral-200 rounded-box shadow-sm") }}
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    style="display:none; z-index:100;"

>
    {{ $slot }}
</div>