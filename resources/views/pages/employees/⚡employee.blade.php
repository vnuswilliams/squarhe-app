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
    @if ($this->company)

        <x-delta-card :cards="$card" />


        <x-ui.tabs variant="non-contained">
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
