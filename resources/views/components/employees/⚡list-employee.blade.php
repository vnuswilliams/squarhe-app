<?php

use App\Concerns\HasTableOptions;
use App\Models\Employee;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new class extends Component
{
    use HasTableOptions;
    use WithoutUrlPagination, WithPagination;

    public $companyId;

    // ─── Computed ─────────────────────────────────────────────────────────────
    public $searchStatus = '';

    public $searchDep = '';

    #[Computed]
    public function employees()
    {
        $paginator = $this->baseQuery()
            ->where('status', 'like', '%'.$this->searchStatus.'%')
            ->where('department', 'like', '%'.$this->searchDep.'%')
            ->when(filled($this->searchQuery), fn ($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn ($q) => $this->applySorting($q))
            ->latest()
            ->paginate(15);

        $this->visibleIds = $paginator->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return $paginator;
    }

    protected function baseQuery()
    {
        return Employee::whereCompanyId($this->companyId);
    }

    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q
                ->where('name', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('job_title', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('department', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('base_salary', 'like', '%'.$this->searchQuery.'%');
        });
    }

    // ─── Actions ──────────────────────────────────────────────────────────────

    public function deleteSelected(): void
    {
        $this->baseQuery()->whereIn('id', $this->selectedIds)->delete();
        $this->deselectAll();
        Flux::toast(variant: 'success', text: __('toast.employee.deleteElem'));
    }

    public function delete(string $employeeId): void
    {
        $employee = $this->baseQuery()->findOrFail($employeeId);
        $employee->delete();
        Flux::toast(variant: 'success', text: __('toast.employee.deleteElem'));
    }
};
?>

<div
    x-data="{
        hiddenCols: $persist([]).as('employees-table-hidden-cols'),
    }"
>
   
    {{-- ─── MAIN TABLE (Sheaf UI) ──────────────────────────────────────────────── --}}

        {{-- Toolbar ──────────────────────────────────────────────────────────── --}}
        <div class="flex justify-between items-center gap-2">

        <div>
            <flux:heading level="1" class="font-bold">Tous les employés</flux:heading>
            <flux:text class="text-gray-300">Visualisez tous les employés de votre structure en un éclair.</flux:text>
        </div>
<div class="flex items-center gap-2">
            {{-- Bulk-delete --}}
            <div style="display:none;" wire:show="selectedIds.length">
                <flux:button
                    wire:click="deleteSelected"
                    wire:confirm="{{ __('Voulez-vous vraiment supprimer les employés sélectionnés ? Cette action est irréversible.') }}"
                    variant="danger"
                   
                    icon="trash"
                >
                    {{ __('Supprimer') }}
                    (<span x-text="$wire.selectedIds.length"></span>)
                </flux:button>
            </div>

              {{-- Search --}}
                <flux:input
                    placeholder="{{ __('Nom, poste, département…') }}"
                    wire:model.live.debounce.300ms="searchQuery"
                />
                <flux:select wire:model.live.debounce.200ms="searchDep">
                    <flux:select.option value="">Filtrer par départment </flux:select.option>
                    @foreach(App\Enums\DepartmentEnum::options() as $value)
                    <flux:select.option value="{{ $value['value'] }}">{{ $value['label'] }} </flux:select.option>

                    @endforeach
                </flux:select>
                <flux:select wire:model.live.debounce.200ms="searchStatus">
                    <flux:select.option value="">Filtrer par statut </flux:select.option>
                    @foreach(App\Enums\StatusEnum::options() as $value)
                    <flux:select.option value="{{ $value['value'] }}">{{ $value['label'] }} </flux:select.option>

                    @endforeach
                </flux:select>
                 {{-- Column visibility --}}
            <x-ui.dropdown checkbox checkboxVariant position="bottom-end">
                <x-slot:button>
                    <flux:button
                        icon="bars-3"
                        tooltip="{{ __('Colonnes visibles') }}"
                    />
                </x-slot:button>
                <x-slot:menu>
                    <x-ui.dropdown.item readOnly>{{ __('Colonnes masquées') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item value="department"  x-model="hiddenCols">{{ __('Département') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.item value="baseSalary"  x-model="hiddenCols">{{ __('Salaire de base') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.item value="startDate"   x-model="hiddenCols">{{ __('Date d\'entrée') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.item value="endDate"   x-model="hiddenCols">{{ __('Date de fin') }}</x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>
            </div>

           

        </div>
<x-container>
    <x-ui.table.container variant="default">

        {{-- Table ────────────────────────────────────────────────────────────── --}}
        <x-ui.table
            wire:loading
            loadOn="pagination, search, sorting"
        >
            <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="employees-table">
                <x-ui.table.columns withCheckAll>

                    {{-- Nom — sortable --}}
                    <x-ui.table.head
                        column="name"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Employé') }}
                    </x-ui.table.head>

                    {{-- Poste — sortable --}}
                    <x-ui.table.head
                        column="job_title"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Poste') }}
                    </x-ui.table.head>

                    {{-- Département — sortable, masquable --}}
                    <x-ui.table.head
                        column="department"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                        x-show="!hiddenCols.includes('department')"
                        x-cloak
                    >
                        {{ __('Département') }}
                    </x-ui.table.head>

                    {{-- Type de contrat — sortable --}}
                    <x-ui.table.head
                        column="contract_type"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Contrat') }}
                    </x-ui.table.head>

                    {{-- Salaire de base — sortable, dropdown, masquable --}}
                    <x-ui.table.head
                        column="base_salary"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                        x-show="!hiddenCols.includes('baseSalary')"
                        x-cloak
                    >
                        {{ __('Salaire de base') }}
                    </x-ui.table.head>

                    {{-- Date d'entrée — sortable, dropdown, masquable --}}
                    <x-ui.table.head
                        column="start_date"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                        x-show="!hiddenCols.includes('startDate')"
                        x-cloak
                    >
                        {{ __('Entrée') }}
                    </x-ui.table.head>
                    <x-ui.table.head
                        column="end_date"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                        x-show="!hiddenCols.includes('endDate')"
                        x-cloak
                    >
                        {{ __('Fin') }}
                    </x-ui.table.head>
                    {{-- Statut — sortable --}}
                    <x-ui.table.head
                        column="status"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Statut') }}
                    </x-ui.table.head>

                    {{-- Actions --}}
                    <x-ui.table.head>{{ __('Actions') }}</x-ui.table.head>

                </x-ui.table.columns>
            </x-ui.table.header>

            <x-ui.table.rows>
                @forelse($this->employees as $employee)
                    <x-ui.table.row
                        :key="$employee->id"
                        :checkboxId="$employee->id"
                        class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
                    >
                        {{-- Employé (avatar + nom) --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar name="{{ $employee->name }}" />
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 leading-tight">
                                        {{ $employee->name }}
                                    </span>
                                    @if($employee->data['niu'] ?? null)
                                        <span class="text-xs text-gray-400 font-mono">
                                            NIU : {{ $employee->data['niu'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </x-ui.table.cell>

                        {{-- Poste --}}
                        <x-ui.table.cell>
                            <span class="text-sm text-gray-700 dark:text-neutral-300">
                                {{ $employee->job_title ?? '—' }}
                            </span>
                        </x-ui.table.cell>

                        {{-- Département (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('department')" x-cloak>
                            <span class="text-sm text-gray-600 dark:text-neutral-400">
                                {{ $employee->department?->label() ?? '—' }}
                            </span>
                        </x-ui.table.cell>

                        {{-- Type de contrat --}}
                        <x-ui.table.cell>
                            <flux:badge color="{{ $employee->contract_type?->color() }}">
                                {{ $employee->contract_type?->label() }}
                            </flux:badge>
                        </x-ui.table.cell>

                        {{-- Salaire de base (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('baseSalary')" x-cloak>
                            <span class="font-mono text-sm font-semibold">
                                {{ number_format($employee->base_salary, 0, ',', ' ') }} F cfa
                            </span>
                        </x-ui.table.cell>

                        {{-- Date d'entrée (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('startDate')" x-cloak>
                            <span class="text-sm font-mono">
                                {{ $employee->start_date?->translatedFormat('d M Y') ?? '—' }}
                            </span>
                        </x-ui.table.cell>

                        <x-ui.table.cell x-show="!hiddenCols.includes('endDate')" x-cloak>
                            <span class="text-sm font-mono">
                                {{ $employee->end_date?->translatedFormat('d M Y') ?? '—' }}
                            </span>
                        </x-ui.table.cell>

                        {{-- Statut --}}
                        <x-ui.table.cell>
                            <flux:badge color="{{ $employee->status?->color() }}">
                                {{ $employee->status?->label() }}
                            </flux:badge>
                        </x-ui.table.cell>

                       

                        {{-- Actions --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button square

                                    href="{{ route('employees.show', ['id' => $employee->id]) }}"
                                    wire:navigate
                                    icon="eye"
                                    variant="primary"
                                    tooltip="{{ __('Voir le profil') }}"
                                />
                                <flux:button square
                                    wire:click="delete('{{ $employee->id }}')"
                                    wire:confirm="{{ __('Supprimer cet employé ? Cette action est irréversible.') }}"
                                    icon="trash"
                                    tooltip="{{ __('Supprimer') }}"
                                />
                            </div>
                        </x-ui.table.cell>

                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>
                        <x-ui.empty>
                            <x-ui.empty.media>
                                <x-ui.icon name="users" class="size-10" />
                            </x-ui.empty.media>
                            <x-ui.empty.contents>
                                <h3 class="text-lg font-semibold">{{ __('Aucun employé trouvé') }}</h3>
                                <p class="text-sm text-neutral-500 mb-4">
                                @if ($searchQuery)
                                        Aucun résultat pour votre recherche " {{ $searchQuery }} ".
                                    @else
                                        {{ __('Aucun employé  ou aucun résultat pour cette recherche, pas de panique.') }}
                                        @endif
                                </p>
            <flux:button variant="primary" href="{{ route('employees.add') }}" wire:navigate >
                Ajouter un employé(e)
            </flux:button>


                            </x-ui.empty.contents>
                        </x-ui.empty>
                    </x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
        {{ $this->employees->links(data: ['scrollTo' => "#table" ]) }}

    </x-ui.table.container>
</x-container>
</div>