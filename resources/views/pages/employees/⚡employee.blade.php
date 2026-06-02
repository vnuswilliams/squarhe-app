<?php

use App\Enums\StatusEnum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Modules employés')] class extends Component
{



    #[Computed]
    public function company()
    {
        return auth()->user()?->company()->withCount('employees')->first();
    }

    public function with(): array
    {
        if (! $this->company) {
            return [
                'card' => [],
            ];
        }

        return [
            'card' => [
                [
                    'label' => 'Effectif total',
                    'current' => $this->company?->employees_count,
                    'delta' => '',
                    'color' => 'blue',
                ],
                [
                    'label' => 'Fin de contrat (mois)',
                    'current' => $this->company?->employees
                        ->where(function ($employee) {
                            return $employee->end_date &&
                            $employee->end_date->month === now()->month &&
                            $employee->end_date->year === now()->year;
                        })->count(),
                    'delta' => '',
                    'color' => 'amber',
                ],
                [
                    'label' => 'Contrats expirés',
                    'current' => $this->company?->employees->where('end_date', '<', now())->count(),
                    'delta' => '',
                    'color' => 'rose',
                ],
                [
                    'label' => 'En congés',
                    'current' => $this->company?->employees->where('status', StatusEnum::ONLEAVE->value)->count(),
                    'delta' => '',
                    'color' => 'emerald',
                ],
            ],
        ];
    }
};
?>

<div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Employé(e)s  </flux:heading>
            <flux:text variant="subtle">Gérez vos ressources</flux:text>
        </div>
        {{-- Indicateur Offline --}}
        <div x-data x-show="!$store.offline.isOnline" class="flex items-center gap-2 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-bold border border-amber-200 dark:border-amber-800">
            <flux:icon.wifi class="size-4" />
            <span>{{ __('offline.offline_badge') }}</span>
        </div>
        <div>
            <flux:button variant="primary" icon="user-plus" href='{{ route("employees.add") }}' wire:navigate />
            <flux:button icon="user-group" href='{{ route("employees.import") }}'  wire:navigate />
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item href="{{ route('employees.import.overtimes') }}" wire:navigate>{{ __('Import HS entreprise') }}</flux:menu.item>
                    <flux:menu.item href="{{ route('employees.import.leaves') }}" wire:navigate>{{ __('Import congés entreprise') }}</flux:menu.item>
                    <flux:menu.item href="{{ route('employees.import.remunerations') }}" wire:navigate>{{ __('Import rémunérations entreprise') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- Vue Offline --}}
    <div x-data x-show="!$store.offline.isOnline" class="mt-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="emp in $store.offline.localData.employees" :key="emp._id">
                <div class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold" x-text="emp.name.charAt(0)"></div>
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white" x-text="emp.name"></h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="emp.job_title"></p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between items-center text-xs">
                        <span class="px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="emp.status"></span>
                        <span class="text-zinc-400" x-text="'ID: ' + emp.id.substring(0,8)"></span>
                    </div>
                </div>
            </template>
        </div>
        <template x-if="$store.offline.localData.employees.length === 0">
            <div class="p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl">
                <flux:icon.user-group class="size-12 mx-auto text-zinc-300 mb-4" />
                <flux:heading>{{ __('offline.no_local_data_title') }}</flux:heading>
                <flux:text>{{ __('offline.no_local_data_body') }}</flux:text>
            </div>
        </template>
    </div>

    @if ($this->company)
        <div x-show="$store.offline.isOnline">
            <x-delta-card :cards="$card" />
        </div>


        <x-ui.tabs variant="non-contained" x-show="$store.offline.isOnline">
            <x-ui.tab.group>
                <x-ui.tab label="Vue d'ensemble" icon="globe-alt" />
                <x-ui.tab label="Tous les employés" icon="users" />
                <x-ui.tab label="En congé" icon="clock" />
                <x-ui.tab label="Fin de contrat" icon="clock" />
                <x-ui.tab label="Contrats expirés" icon="document-minus" />
            </x-ui.tab.group>
            <x-ui.tab.panel>
                </x-ui.tab.panel>

            <x-ui.tab.panel>
                <livewire:employees.list-employee :company="$this->company" />
            </x-ui.tab.panel>

            <x-ui.tab.panel>

                <livewire:employees.list-employee-onleave :company="$this->company" />
                </x-ui.tab.panel>
            <x-ui.tab.panel>
                <livewire:employees.list-employee-expiring :company="$this->company" />

            </x-ui.tab.panel>

            <x-ui.tab.panel>

                <livewire:employees.list-employee-expired :company="$this->company" />
                </x-ui.tab.panel>


        </x-ui.tabs>
    @else
        <x-no-company />
    @endif
</div>
