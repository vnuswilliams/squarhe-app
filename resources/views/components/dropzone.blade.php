@props([
    'accept' => '*',
    'uploaded' => false,
])

@php
    $id = md5($attributes->wire('model'));
    $model = $attributes->wire('model')->value();
@endphp

{{--  usage
<x-ui.dropzone
    wire:model="file"
    accept=".xlsx,.xls,.csv"
    :uploaded="$tempPath"
>
    <x-slot:empty>
        Importez un fichier Excel
    </x-slot:empty>

    <x-slot:success>
        {{ $totalRows }} ligne(s) détectée(s)
    </x-slot:success>
</x-ui.dropzone>
--}}

<div
    x-data="{
        dragging: false,

        handleDrop(event) {
            this.dragging = false

            const files = event.dataTransfer.files

            if (!files.length) return

            const input = this.$refs.input

            input.files = files

            input.dispatchEvent(
                new Event('change', { bubbles: true })
            )
        }
    }"

    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="handleDrop($event)"

    :class="dragging
        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
        : 'border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40'"

    class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-10 text-center transition-colors cursor-pointer"
>

    <label
        for="dropzone-{{ $id }}"
        class="absolute inset-0 cursor-pointer"
    ></label>

    <input
        x-ref="input"
        id="dropzone-{{ $id }}"
        type="file"
        accept="{{ $accept }}"
        {{ $attributes }}
        class="sr-only"
    />

    @if($uploaded)

        <flux:icon.document-check
            class="w-10 h-10 text-green-500"
        />

        <div class="space-y-1">
            <p class="font-medium text-gray-700 dark:text-gray-200">
                {{ $success ?? __('Fichier chargé') }}
            </p>

            <p class="text-sm text-zinc-500">
                Cliquez pour remplacer le fichier
            </p>
        </div>

    @else

        <flux:icon.cloud-arrow-up
            class="w-10 h-10 text-zinc-400"
        />

        <div class="space-y-1">
            <p class="font-medium text-gray-700 dark:text-gray-200">
                {{ $empty ?? __('Glissez-déposez un fichier ici') }}
            </p>

            <p class="text-sm text-zinc-500">
                {{ __('Ou cliquez pour parcourir') }}
            </p>
        </div>

    @endif

    <div
        wire:loading
        wire:target="{{ $model }}"
        class="flex items-center gap-2 text-sm text-zinc-500"
    >
        <svg
            class="animate-spin w-4 h-4"
            viewBox="0 0 24 24"
            fill="none"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />

            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8z"
            />
        </svg>

        {{ __('Uploading du fichier...') }}
    </div>
</div>