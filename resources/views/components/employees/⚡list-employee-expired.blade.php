<?php

use App\Concerns\HasTableOptions;
use App\Enums\ContractTypeEnum;
use App\Models\Employee;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination, WithoutUrlPagination;
    use HasTableOptions;

    public $companyId;
    //─── Computed ─────────────────────────────────────────────────────────────
    #[Computed]
    public function employees()
    {
        $paginator = $this->baseQuery()
            ->when(filled($this->searchQuery), fn($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn($q) => $this->applySorting($q))
            ->latest()
            ->paginate(15);

        $this->visibleIds = $paginator->pluck("id")->map(fn($id) => (string) $id)->toArray();

        return $paginator;
    }

    protected function baseQuery()
    {
        return Employee::whereCompanyId($this->companyId)->where("end_date", "<", now());
    }
    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where("name", "like", "%" . $this->searchQuery . "%")
                ->orWhere("job_title", "like", "%" . $this->searchQuery . "%")
                ->orWhere("department", "like", "%" . $this->searchQuery . "%")
                ->orWhere("base_salary", "like", "%" . $this->searchQuery . "%");
        });
    }
};
?>

<div x-data="{
    hiddenCols: $persist([]).as('employees-table-hidden-cols'),
}">
  {{-- Toolbar ──────────────────────────────────────────────────────────── --}}
        <div class="flex justify-between items-center gap-2">

            <div>
                <flux:heading level="1" class="font-bold">Employé(e)s en fin de contrat</flux:heading>
                <flux:text class="text-gray-300">Visualisez et gérer tous les employés dont contrat a expiré.                </flux:text>
            </div>
            <div class="flex items-center gap-2">
                {{-- Search --}}
                <flux:input placeholder="{{ __('Nom, poste, département…') }}"
                    wire:model.live.debounce.300ms="searchQuery" />


            </div>



        </div>
   <x-container>
   {{-- ─── MAIN TABLE (Sheaf UI) ──────────────────────────────────────────────── --}}
    <x-ui.table.container variant="default">

      

        {{-- Table ────────────────────────────────────────────────────────────── --}}
        <x-ui.table wire:loading loadOn="pagination, search, sorting">
            <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="employees-table">
                <x-ui.table.columns>

                    {{-- Nom — sortable --}}
                    <x-ui.table.head column="name" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Employé") }}
                    </x-ui.table.head>

                    {{-- Poste — sortable --}}
                    <x-ui.table.head column="job_title" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Poste") }}
                    </x-ui.table.head>

                    {{-- Type de contrat — sortable --}}
                    <x-ui.table.head column="contract_type" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Contrat") }}
                    </x-ui.table.head>

                    {{-- Date d'entrée — sortable, dropdown, masquable --}}
                    <x-ui.table.head column="start_date" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Entrée") }}
                    </x-ui.table.head>
                    <x-ui.table.head column="end_date" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Fin") }}
                    </x-ui.table.head>
                   


                    {{-- Actions --}}
                    <x-ui.table.head>{{ __("Actions") }}</x-ui.table.head>

                </x-ui.table.columns>
            </x-ui.table.header>

            <x-ui.table.rows>
                @forelse($this->employees as $employee)
                    <x-ui.table.row :key="$employee->id"
                        class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                        {{-- Employé (avatar + nom) --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar name="{{ $employee->name }}" />
                                <div class="flex flex-col">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 leading-tight">
                                        {{ $employee->name }}
                                    </span>
                                    @if ($employee->data["niu"] ?? null)
                                        <span class="text-xs text-gray-400 font-mono">
                                            NIU : {{ $employee->data["niu"] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </x-ui.table.cell>

                        {{-- Poste --}}
                        <x-ui.table.cell>
                            <span class="text-sm text-gray-700 dark:text-neutral-300">
                                {{ $employee->job_title ?? "—" }}
                            </span>
                        </x-ui.table.cell>


                        {{-- Type de contrat --}}
                        <x-ui.table.cell>
                            <flux:badge color="{{ $employee->contract_type?->color() }}">
                                {{ $employee->contract_type?->label() }}
                            </flux:badge>
                        </x-ui.table.cell>

                        {{-- Date d'entrée (masquable) --}}
                        <x-ui.table.cell>
                            <span class="text-sm font-mono">
                                {{ $employee->start_date?->translatedFormat("d M Y") ?? "—" }}
                            </span>
                        </x-ui.table.cell>

                        <x-ui.table.cell>
                            <span class="text-sm font-mono">
                                {{ $employee->end_date?->translatedFormat("d M Y") ?? "—" }}
                            </span>
                        </x-ui.table.cell>
                       

                        {{-- Actions --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button square href="{{ route('employees.show', ['id' => $employee->id]) }}"
                                    wire:navigate icon="eye" variant="primary"
                                    tooltip="{{ __('Voir le profil') }}" />

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
                                <h3 class="text-lg font-semibold">{{ __("Aucun employé trouvé") }}</h3>
                                <p class="text-sm text-neutral-500 mb-4">
                                    @if ($searchQuery)
                                        Aucun résultat pour votre recherche " {{ $searchQuery }} ".
                                    @else
                                        {{ __("Le contrat d'aucun de vos employés n'est arrivé a terme.") }}
                                    @endif
                                </p>
                                <flux:button variant="primary" href="{{ route('employees.add') }}" wire:navigate>
                                    Ajouter un employé(e)
                                </flux:button>


                            </x-ui.empty.contents>
                        </x-ui.empty>
                    </x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
        {{ $this->employees->links(data: ["scrollTo" => "#table"]) }}

    </x-ui.table.container>
   </x-container>
</div>
