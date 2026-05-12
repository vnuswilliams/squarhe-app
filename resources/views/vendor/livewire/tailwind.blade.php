@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">

            {{-- Showing X to Y of Z results --}}
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Showing') }}
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $paginator->firstItem() }}</span>
                {{ __('to') }}
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $paginator->lastItem() }}</span>
                {{ __('of') }}
                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $paginator->total() }}</span>
                {{ __('results') }}
            </p>

            {{-- Boutons Précédent / Suivant --}}
            <div class="flex gap-2">
                <flux:button
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    icon="chevron-left"
                    variant="outline"
                    :disabled="$paginator->onFirstPage()"
                    tooltip="{{ __('pagination.previous') }}"

                    />

                <flux:button
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    icon-trailing="chevron-right"
                    variant="outline"
                    :disabled="! $paginator->hasMorePages()"
                    tooltip="{{ __('pagination.next') }}"
                    />
            </div>

        </nav>
    @endif
</div>