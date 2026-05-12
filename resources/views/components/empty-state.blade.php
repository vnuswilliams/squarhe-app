@props([
    'title' => 'Oops sorry', 'message' => 'There no data to display.'
    ])
<div class="flex flex-col items-center justify-center py-12 text-center">
    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-neutral-100 dark:bg-neutral-800 mb-2">
                <flux:icon name="inbox" class="size-15" />
    </div>
    <p class="dark:text-zinc-400 text-zinc-900 font-medium">{{ $title }}</p>
                <flux:text variant="subtle">
                    {{ $message }}
                </flux:text>

{{--     <p class="text-sm text-zinc-500">{{ $message }}</p> --}}
</div>
