<?php

use App\Concerns\HasTableOptions;
use App\Models\Leave;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use  Carbon\Carbon;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Services\CalculateDays;
use App\Enums\LeaveTypeEnum;


new class extends Component
{
    use HasTableOptions, WithoutUrlPagination,      WithPagination;
    public EmployeeLeaveForm $form;

    public $ids;

    public $employees;

    #[Computed]
    public function baseQuery()
    {
        return Leave::whereIn('employee_id', $this->ids);
    }

    public $filterType = '';

    public $filterStatus = '';

    public $filterEmployee = '';

    #[Computed]
    public function leaves()
    {
        $paginator = $this->baseQuery()
            ->where('employee_id', 'like', '%'.$this->filterEmployee.'%')
            ->where('type', 'like', '%'.$this->filterType.'%')
            ->where('status', 'like', '%'.$this->filterStatus.'%')
            ->when(filled($this->searchQuery), fn ($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn ($q) => $this->applySorting($q))
            ->latest()
            ->paginate(20);

        // Requis par WithSelection pour "tout sélectionner sur la page"
        $this->visibleIds = $paginator->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        return $paginator;
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

    /**
     * Suppression en masse des lignes sélectionnées.
     */
    public function deleteSelected()
    {
        $wantToDelete = $this->baseQuery()->whereIn('id', $this->selectedIds)->get();

        foreach ($wantToDelete as $deleteLeave) {
            Gate::authorize('delete', [Leave::class, $deleteLeave]);
            $deleteLeave->delete();
        }
        $this->deselectAll();
        Flux::toast(variant: 'success', text: 'Les absences/congés sélectionnés ont été supprimés.');

    }

    public function edit($leaveId)
    {
        $leaveToUpdate = $this->baseQuery()->whereId($leaveId)->firstOrFail();
        $this->form->setLeave($leaveToUpdate);
        Flux::modal("edit-leave-modal")->show();
    }

    public function update()
    {
        $this->form->days = app(CalculateDays::class)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->update();
        $this->form->reset();
        Flux::modal("edit-leave-modal")->close();
        Flux::toast(variant: "success", text: "Votre absence ou congé a été mis à jour avec succès.");
    }

    public $leaveToDelete = null;

    public function confirmBeforeDelete($idLeaveWeWantToDelete)
    {
        $this->leaveToDelete = $this->baseQuery->whereId($idLeaveWeWantToDelete)
        ->first();

        if ($this->leaveToDelete) {
            Flux::modal("delete-leave-modal")->show();
            return;
        }

        Flux::toast(variant: "warning", text: __("toast.deleteNotFound"));
    }

    
    public function delete()
    {
        if ($this->leaveToDelete) {
            Gate::authorize("delete", [Leave::class, $this->leaveToDelete]);
            $this->leaveToDelete->delete();
            Flux::toast(variant: "success", text: "Votre absence ou congé a été supprimé avec succès.");
            Flux::modal("delete-leave-modal")->close();
            $this->leaveToDelete = null;
        }
    }
}; ?>

<div>



{{-- ─── MAIN TABLE (Sheaf UI) ─── --}}
<div class="flex justify-between items-center gap-2">
<div>

    <flux:heading level="1" class="font-bold">Tous les absences de votre compagnie</flux:heading>
    <flux:text class="text-gray-300">Visualisez tous les absneces et congés de votre structure en un éclair.</flux:text>
</div>
    
</div>
    <x-container>
        <x-ui.table.container variant="default">
            {{-- Toolbar : bulk delete | search | column visibility --}}
            <div class="flex items-center gap-2">

                {{-- Bulk-delete : visible seulement quand des lignes sont sélectionnées --}}
                <div style="display:none;" wire:show="selectedIds.length">
                    <flux:button wire:click="deleteSelected"
                        wire:confirm="{{ __('Voulez-vous vraiment supprimer les absences/congés sélectionnés ? Cette action est irréversible.') }}"
                        variant="danger" icon="trash">
                        (<span x-text="$wire.selectedIds.length"></span>)
                    </flux:button>
                </div>

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
                    <x-ui.table.columns withCheckAll>

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

                                        {{-- Actions --}}
                        <x-ui.table.head>{{ __("Actions") }}</x-ui.table.head>

                    </x-ui.table.columns>
                </x-ui.table.header>

                <x-ui.table.rows>
                    @forelse($this->leaves as $leave)
                        <x-ui.table.row :key="$leave->id" :checkboxId="$leave->id"
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

                                                {{-- Actions --}}
                            <x-ui.table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="edit({{ $leave->id }})" size="sm" variant="ghost"
                                        icon="pencil" tooltip="{{ __('Modifier') }}" />
                                    <flux:button wire:click="confirmBeforeDelete({{ $leave->id }})" size="sm"
                                        variant="ghost" icon="trash" tooltip="{{ __('Supprimer') }}" />
                                </div>
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
    
    {{-- ─── MODAL : DELETE ─── --}}
    <flux:modal name="delete-leave-modal">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">{{ __("Supprimer ce congé ou cette absence") }}</flux:heading>
            @if ($leaveToDelete)
                <p>
                    Voulez-vous vraiment supprimer <strong>{{ $leaveToDelete->type->label() }}</strong>
                    allant du <strong>{{ $leaveToDelete->start_date?->translatedFormat("d M Y") }}</strong>
                    au <strong>{{ $leaveToDelete->end_date?->translatedFormat("d M Y") }}</strong> ?
                </p>
                <p class="text-sm text-red-600 dark:text-red-400">Cette action est irréversible.</p>
            @endif
            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button>{{ __("Cancel") }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">{{ __('Oui, j\'en suis sûr') }}</flux:button>
            </div>
        </div>
    </flux:modal>


{{-- ─── MODAL : EDIT ─── --}}
<flux:modal name="edit-leave-modal" class="min-w-225">
    <div class="space-y-6 pt-5">
        <flux:heading size="lg">{{ __("Mettre à jour un congé ou une absence") }}</flux:heading>
        <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
            <div class="grid sm:grid-cols-3 gap-4">
            <flux:select wire:model="form.employee_id" label="Ajouter une absencse ou congés à ?">
                        <option value="">Choisir un collaborateur</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->shortName }}</option>
                        @endforeach

                    </flux:select>
                <flux:select label="{{ __('Type de congé') }}" wire:model="form.type">
                    <option>{{ __("Choisir un type") }}</option>
                    @foreach (LeaveTypeEnum::options() as $case)
                        <option value="{{ $case['value'] }}">{{ $case["label"] }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="form.approved_by" label="{{ __('Approuvé par') }}" />

                @if (in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                    <flux:input wire:model="form.last_leave" type="date"
                        label="{{ __('Date du dernier congé annuel (optionnel)') }}" />
                @endif

                <flux:input type="date" label="{{ __('Date de début') }}" wire:model="form.start_date" />
                <flux:input type="date" label="{{ __('Date de fin') }}" wire:model="form.end_date" />
            </div>

            <flux:textarea label="{{ __('Notes') }}" wire:model="form.notes" />

            <div class="flex justify-end gap-2 pt-4">
                <flux:button type="submit" variant="primary">{{ __("Enregistrer") }}</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
</div>