<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Support')] class extends Component {
    public string $search = '';


    #[Computed]
    public function tutorials(): Collection
    {
        $tutorialPath = resource_path('tutorials');

        if (! is_dir($tutorialPath)) {
            return collect();
        }

        $parsedown = new \Parsedown;
        $parsedown->setSafeMode(true);

        $files = collect(glob($tutorialPath.'/*.md') ?: [])
            ->map(function (string $filePath) use ($parsedown): array {
                $raw = file_get_contents($filePath) ?: '';
                $title = Str::of($raw)
                    ->match('/^#\s+(.+)$/m')
                    ->trim()
                    ->value();

                $title = $title !== '' ? $title : Str::of(pathinfo($filePath, PATHINFO_FILENAME))->replace('-', ' ')->title()->value();

                return [
                    'slug' => Str::slug(pathinfo($filePath, PATHINFO_FILENAME)),
                    'title' => $title,
                    'excerpt' => Str::of(strip_tags($parsedown->text($raw)))->squish()->limit(140)->value(),
                    'content' => $parsedown->text($raw),
                ];
            })
            ->sortBy('title')
            ->values();

        if (blank($this->search)) {
            return $files;
        }

        return $files
            ->filter(fn (array $tutorial) => Str::contains(Str::lower($tutorial['title'].' '.$tutorial['excerpt']), Str::lower($this->search)))
            ->values();
    }
};
?>

<div class="space-y-8">
    <section class="rounded-2xl border border-zinc-200 bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-500 p-8 text-white shadow-lg dark:border-zinc-700">
        <flux:heading level="1" class="text-2xl font-bold text-white">{{ __('support.title') }}</flux:heading>
        <flux:text class="mt-3 max-w-2xl text-white/90">{{ __('support.subtitle') }}</flux:text>

        <div class="mt-6 max-w-xl">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('support.search_placeholder')" />
        </div>
    </section>

    <section>
        <div class="mb-4 flex items-center justify-between">
            <flux:heading level="2" class="font-semibold">{{ __('support.start_tutorials') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ trans_choice('support.result_count', $this->tutorials->count(), ['count' => $this->tutorials->count()]) }}</flux:text>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($this->tutorials as $tutorial)
                <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading level="3" class="text-base font-semibold">{{ $tutorial['title'] }}</flux:heading>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $tutorial['excerpt'] }}</p>

                    <details class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/70">
                        <summary class="cursor-pointer text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('support.read_tutorial') }}</summary>
                        <div class="prose prose-sm mt-3 max-w-none dark:prose-invert">
                            {!! $tutorial['content'] !!}
                        </div>
                    </details>
                </article>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-zinc-300 p-8 text-center text-zinc-500 dark:border-zinc-600 dark:text-zinc-300">
                    {{ __('support.no_results') }}
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:heading level="2" class="font-semibold">{{ __('support.useful_links') }}</flux:heading>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <a href="mailto:support@squarheapp.test" class="rounded-lg border border-zinc-200 p-4 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('support.links.contact') }}</a>
            <a href="{{ route('employees') }}" wire:navigate class="rounded-lg border border-zinc-200 p-4 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('support.links.employees') }}</a>
            <a href="{{ route('documents') }}" wire:navigate class="rounded-lg border border-zinc-200 p-4 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">{{ __('support.links.documents') }}</a>
        </div>
    </section>
</div>
