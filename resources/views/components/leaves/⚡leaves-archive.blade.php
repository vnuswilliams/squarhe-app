<?php

use App\Concerns\HasTableOptions;
use App\Models\LeaveSnapshot;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Services\CalculateDays;
use App\Enums\LeaveTypeEnum;

new class extends Component
{
    use HasTableOptions, WithoutUrlPagination, WithPagination;

    public $ids;

    public $employees;
public $company;
    #[Computed]
    public function baseQuery()
    {
        
        if (! $this->company) {
            return LeaveSnapshot::query()->whereRaw('1 = 0');
        }

        return LeaveSnapshot::whereHas('payrollClosure', function ($query) {
            $query->where('company_id', $this->company->id);
        });
    }

    public $filterType = '';

    public $filterStatus = '';

    public $filterEmployee = '';

    #[Computed]
    public function leaves()
    {
        return $this->baseQuery()
            ->when(filled($this->filterEmployee), fn ($q) => $q->where('employee_id', $this->filterEmployee))
            ->when(filled($this->filterType), fn ($q) => $q->where('type', $this->filterType))
            ->when(filled($this->filterStatus), fn ($q) => $q->where('status', $this->filterStatus))
            ->when(filled($this->searchQuery), fn ($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn ($q) => $this->applySorting($q))
            ->latest()
            ->paginate(20);
    }

    /**
     * Sheaf WithSearch : colonnes interrogées.
     */
    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where('notes', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('type', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('status', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('approved_by', 'like', '%'.$this->searchQuery.'%');
        });
    }
}; ?>

<div>



{{-- ─── MAIN TABLE (Sheaf UI) ─── --}}
<div class="flex justify-between items-center gap-2">
<div>

    <flux:heading level="1" class="font-bold">Tous les absences archivés de votre compagnie</flux:heading>
    <flux:text class="text-gray-300">Visualisez tous les absneces et congés de votre structure en un éclair.</flux:text>
</div>
    
</div>
    <x-container>
        <x-ui.table.container variant="default">
            {{-- Toolbar : bulk delete | search | column visibility --}}
            <div class="flex items-center gap-2">

                               {{-- Search --}}
                
                    <flux:input class="[&_input]:bg-transparent" placeholder="{{ __('Rechercher...') }}"
                        leftIcon="magnifying-glass" wire:model.live.debounce.300ms="searchQuery" />
            
                        <flux:select wire:model.live.debounce.200ms="filterEmployee">
                    <flux:select.option value="">Filtrer par collaborateur </flux:select.option>
                    @foreach($employees as $emp)
                    <flux:select.option value="{{ $emp->id }}">{{ $emp->name }} </flux:select.option>

                    @endforeach
                </flux:select>    
                <flux:select wire:model.live.debounce.200ms="filterType">
                    <flux:select.option value="">Filtrer par type </flux:select.option>
                    @foreach(App\Enums\LeaveTypeEnum::options() as $value)
                    <flux:select.option value="{{ $value['value'] }}">{{ $value['label'] }} </flux:select.option>
                    @endforeach
                </flux:select> 
                <flux:select wire:model.live.debounce.200ms="filterStatus">
                    <flux:select.option value="">Filtrer par statut </flux:select.option>
                    @foreach(App\Enums\StatusEnum::options() as $value)
                    <flux:select.option value="{{ $value['value'] }}">{{ $value['label'] }} </flux:select.option>

                    @endforeach
                </flux:select>
            
            </div>
            {{-- Table --}}
            <x-ui.table  wire:loading loadOn="pagination, search, sorting">
                <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="table">
                    <x-ui.table.columns>

                        {{-- Type — sortable --}}
                        <x-ui.table.head column="type" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Type") }}
                        </x-ui.table.head>

                        {{-- Date début — sortable, dropdown --}}
                        <x-ui.table.head column="start_date" sortable :currentSortBy="$sortBy"
                            :currentSortDir="$sortDir">
                            {{ __("Date de début") }}
                        </x-ui.table.head>

                        {{-- Date fin — sortable, dropdown --}}
                        <x-ui.table.head column="end_date" sortable :currentSortBy="$sortBy"
                            :currentSortDir="$sortDir">
                            {{ __("Date de fin") }}
                        </x-ui.table.head>

                        {{-- Jours — sortable --}}
                        <x-ui.table.head column="days" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Jours") }}
                        </x-ui.table.head>

                        {{-- Statut — sortable --}}
                        <x-ui.table.head column="status" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Statut") }}
                        </x-ui.table.head>

                        {{-- Approuvé par (masquable) --}}
                        <x-ui.table.head column="approved_by" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                            {{ __("Approuvé par") }}
                        </x-ui.table.head>
                    </x-ui.table.columns>
                </x-ui.table.header>

                <x-ui.table.rows>
                    @forelse($this->leaves as $leave)
                        <x-ui.table.row :key="$leave->id"
                            class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            {{-- Type --}}
                            <x-ui.table.cell>
                                <flux:heading class="font-medium">
                                    {{ $leave->type->label() }}
                                    @if ($leave->notes)
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                                        <flux:tooltip.content>{{ $leave->notes }}</flux:tooltip.content>
                                    </flux:tooltip>
                                @endif
                            </flux:heading>
                            </x-ui.table.cell>

                            {{-- Date de début --}}
                            <x-ui.table.cell>
                                <span class="text-sm font-mono">
                                    {{ Carbon::parse($leave->start_date)->translatedFormat("d M Y") }}
                                </span>
                            </x-ui.table.cell>

                            {{-- Date de fin --}}
                            <x-ui.table.cell>
                                <span class="text-sm font-mono">
                                    {{ Carbon::parse($leave->end_date)->translatedFormat("d M Y") }}
                                </span>
                            </x-ui.table.cell>

                            {{-- Jours --}}
                            <x-ui.table.cell>
                                <span class="font-semibold text-sm">{{ $leave->days }}
                                    jr{{ $leave->days > 1 ? "s" : "" }}</span>
                            </x-ui.table.cell>

                            {{-- Statut --}}
                            <x-ui.table.cell>
                                <flux:badge color="{{ $leave->status->color() }}">
                                    {{ $leave->status->label() }}
                                </flux:badge>
                            </x-ui.table.cell>

                            {{-- Approuvé par (masquable) --}}
                            <x-ui.table.cell>
                                <span class="text-sm text-gray-500 dark:text-neutral-400">
                                    {{ $leave->approved_by ?? "—" }}
                                </span>
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @empty
                        <x-ui.table.empty>
                            <x-ui.empty>
                                <x-ui.empty.media>
                                    <x-ui.icon name="inbox" class="size-10" />
                                </x-ui.empty.media>
                                <x-ui.empty.contents>
                                    <h3 class="text-lg font-semibold">{{ __("Aucune absence ou congé trouvé") }}</h3>
                                    <p class="text-sm text-neutral-500">
                                        {{ __("Ajoutez une absence ou un congé pour commencer.") }}
                                    </p>
                                </x-ui.empty.contents>
                            </x-ui.empty>
                        </x-ui.table.empty>
                    @endforelse
                </x-ui.table.rows>
            </x-ui.table>
            {{ $this->leaves->links(data: ['scrollTo' => "#table" ]) }}
        </x-ui.table.container>
        
    </x-container>
    
</div>