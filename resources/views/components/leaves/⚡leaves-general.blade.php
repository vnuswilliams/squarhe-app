<?php

use App\Charts\LeaveChart;
use Livewire\Component;

new class extends Component {
    public $company;

    public string $activeChart = 'department';

    public function updatedActiveChart(): void
    {
        $this->dispatch('larapex:refresh');
    }

    public function with(): array
    {
        $chart = match ($this->activeChart) {
            'employee' => app(LeaveChart::class, ['company' => $this->company])->leavePerEmployee(),
            'type' => app(LeaveChart::class, ['company' => $this->company])->leavePerType(),
            'status' => app(LeaveChart::class, ['company' => $this->company])->leavePerStatus(),
            default => app(LeaveChart::class, ['company' => $this->company])->leavePerDepertment(),
        };

        return [
            'chart' => $chart,
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 ">
        <div>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Statistiques des absences') }}</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Visualisez les données d\'absences sous différentes perspectives.') }}</p>
        </div>
        <div class="w-full sm:w-72">
            <flux:select wire:model.live="activeChart" >
                <flux:select.option value="status">{{ __('Par statut') }}</flux:select.option>
                <flux:select.option value="department">{{ __('Par département') }}</flux:select.option>
                <flux:select.option value="employee">{{ __('Par collaborateur') }}</flux:select.option>
                <flux:select.option value="type">{{ __('Par type d\'absence') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

        <x-container>
            {!! $chart->container() !!}
        </x-container>
</div>