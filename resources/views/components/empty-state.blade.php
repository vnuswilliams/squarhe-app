@props([
    'title' => 'Oops sorry', 'message' => 'There no data to display.'
    ])
<div class="flex flex-col items-center justify-center py-12 text-center">
    <flux:icon name="check-circle" class="size-8 text-zinc-300 mb-2" />
    <p class="dark:text-zinc-400 text-zinc-900 font-medium">{{ $title }}</p>
                <flux:text variant="subtle">
                    {{ $message }}
                </flux:text>

{{--     <p class="text-sm text-zinc-500">{{ $message }}</p> --}}
</div>
