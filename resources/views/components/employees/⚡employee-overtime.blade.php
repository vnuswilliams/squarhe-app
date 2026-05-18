<?php

use App\Concerns\HasTableOptions;
use App\Enums\HsuppEnum;
use App\Jobs\ImportEmployeeOvertimesJob;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Livewire\Forms\EmployeeOvertimeForm;
use App\Models\Overtime;
use App\Services\CalculateHsupp;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use WithFileUploads;
    use WithPagination, WithoutUrlPagination;
    use HasTableOptions;

    public $employee;

    public $importFile;

    public array $previewRows = [];

    public array $importErrors = [];

    public bool $readyToImport = false;

    public EmployeeOvertimeForm $form;

    public function mount()
    {
        $this->form->hours_rate = app(CalculateHsupp::class)->hourRate($this->employee);
    }

    /**
     * Paginated + searchable + sortable overtimes pour le tableau principal.
     */
    #[Computed]
    public function overtimes()
    {
        $paginator = $this->baseQuery()
            ->when(filled($this->searchQuery), fn ($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn ($q) => $this->applySorting($q))
            ->latest()
            ->paginate(10);

        $this->visibleIds = $paginator->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return $paginator;
    }

    /**
     * Toutes les overtime sans pagination — pour les delta cards.
     */
    #[Computed]
    public function overtimeStats()
    {
        return $this->employee->overtimes ?? collect();
    }

    /**
     * Requête Eloquent de base partagée.
     */
    protected function baseQuery()
    {
        return Overtime::query()->where('employee_id', $this->employee->id);
    }

    /**
     * Sheaf WithSearch : colonnes interrogées.
     */
    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where('day_type', 'like', '%'.$this->searchQuery.'%')
              ->orWhere('notes', 'like', '%'.$this->searchQuery.'%')
              ->orWhere('added_by', 'like', '%'.$this->searchQuery.'%')
              ->orWhere('week', 'like', '%'.$this->searchQuery.'%');
        });
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->multiplier = HsuppEnum::from($this->form->day_type)->dayType();
        $this->form->create();
        Flux::toast(variant: 'success', text: __('toast.ov.addOvSuccess'));
        $this->form->resetExcept('hours_rate');
    }

    public function edit($overtimeId)
    {
        $overtimeToUpdate = Overtime::whereId($overtimeId)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();

        $this->form->setOvertime($overtimeToUpdate);
        Flux::modal('edit-overtime-modal')->show();
    }

    public function update()
    {
        $this->form->multiplier = HsuppEnum::from($this->form->day_type)->dayType();
        $this->form->update();
        Flux::modal('edit-overtime-modal')->close();
        Flux::toast(variant: 'success', text: __('toast.ov.updateOvSuccess'));
        $this->form->resetExcept('hours_rate');
    }

    public $overtimeToDelete = null;

    public function confirmBeforeDelete($idOvertimeWeWantToDelete)
    {
        $this->overtimeToDelete = Overtime::whereId($idOvertimeWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->first();

        if ($this->overtimeToDelete) {
            Flux::modal('delete-overtime-modal')->show();
            return;
        }

        Flux::toast(variant: 'warning', text: __('toast.deleteNotFound'));
    }

    public function delete()
    {
        if ($this->overtimeToDelete) {
            Gate::authorize('delete', [Overtime::class, $this->overtimeToDelete]);
            $this->overtimeToDelete->delete();
            Flux::toast(variant: 'success', text: __('toast.ov.deleteOvSuccess'));
            Flux::modal('delete-overtime-modal')->close();
            $this->overtimeToDelete = null;
        }
    }

    /**
     * Suppression en masse des lignes sélectionnées.
     */
    public function deleteSelected()
    {
        Gate::authorize('delete', Overtime::class);

        $this->baseQuery()
            ->whereIn('id', $this->selectedIds)
            ->delete();

        $this->deselectAll();
        Flux::toast(variant: 'success', text: __('toast.ov.deleteOvSuccess'));
    }

    public $snapshotRef = '';

    #[Computed]
    public function overtimesSnapshot()
    {
        $query = $this->employee->overtimesSnapshot;

        if (filled($this->snapshotRef)) {
            $query->where('ref', 'like', '%'.trim($this->snapshotRef).'%');
        }

        return $query ?? [];
    }

    public function exportOvertimeArchives()
    {
        $rows = $this->overtimesSnapshot->map(fn ($snapshot) => [
            __('Ref') => $snapshot->ref,
            __('Semaine') => $snapshot->week,
            __('Type') => $snapshot->day_type?->label(),
            __('Heures') => $snapshot->hours,
            __('Taux horaire') => $snapshot->hours_rate,
            __('Alloc estimés') => $snapshot->alloc,
        ]);

        return (new FastExcel($rows))->download('archives_heures_supp_'.$this->employee->id.'_'.now()->format('m_Y').'.xlsx');
    }

    public function previewImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        $rows = (new FastExcel)->import($this->importFile->getRealPath());
        $this->previewRows = [];
        $this->importErrors = [];

        foreach ($rows as $index => $row) {
            $data = [
                'day_type' => $row['day_type'] ?? null,
                'hours' => $row['hours'] ?? null,
                'hours_rate' => $row['hours_rate'] ?? null,
                'week' => $row['week'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];

            $validator = Validator::make($data, [
                'day_type' => ['required', Rule::in(HsuppEnum::values())],
                'hours' => ['required', 'numeric', 'min:1'],
                'hours_rate' => ['required', 'numeric', 'min:1'],
                'week' => ['required', 'numeric', 'regex:/^[1-5]$/'],
                'notes' => ['nullable', 'string', 'max:100'],
            ]);

            if ($validator->fails()) {
                $this->importErrors[] = ['line' => $index + 2, 'errors' => $validator->errors()->all()];
            }

            $this->previewRows[] = $data;
        }

        $this->readyToImport = count($this->importErrors) === 0 && count($this->previewRows) > 0;
    }

    public function confirmImport(): void
    {
        if (! $this->readyToImport) {
            Flux::toast(variant: 'danger', text: __('toast.ov.launchImportFail'));
            return;
        }

        $path = $this->importFile->store('imports');
        ImportEmployeeOvertimesJob::dispatch($path, $this->employee->id);
        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('toast.ov.launchImport'));
    }

    public function downloadTemplate()
    {
        $path = 'templates/overtimes_import_template.xlsx';

        if (! Storage::exists($path)) {
            $rows = collect([[
                'day_type' => HsuppEnum::HEURE_SUPP_120->value,
                'hours' => 2,
                'hours_rate' => 1500,
                'week' => 1,
                'notes' => 'Exemple',
            ]]);

            (new FastExcel($rows))->export(Storage::path($path));
        }

        return Storage::download($path);
    }
};
?>

<div x-data="{ activeForm: null }">

    {{-- ─── PAGE HEADER ─── --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-bold text-lg">{{ __('Heures supplémentaires') }}</h3>
            <p class="text-gray-400 text-sm">{{ __('Gérez les heures supplémentaires de votre collaborateur') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button @click="activeForm = activeForm === 'a' ? null : 'a'" variant="primary" icon="plus" tooltip="{{ __('Ajouter des heures supp.') }}" />
            <flux:button @click="activeForm = activeForm === 'archives-overtimes' ? null : 'archives-overtimes'" icon="archive-box" tooltip="{{ __('Voir les archives') }}" />
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item @click="activeForm = 'b'">{{ __('Importer des heures supps') }}</flux:menu.item>
                    <flux:menu.item wire:click="downloadTemplate">{{ __('Télécharger le template') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- ─── FORM : ADD OVERTIME ─── --}}
    <x-container x-show="activeForm === 'a'" x-transition>
        <flux:heading level="1" size="lg" class="mb-5">{{ __('Ajouter des heures supplémentaires') }}</flux:heading>
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-4 p-2">
                <flux:input wire:model="form.week" label="{{ __('Numéro de la semaine') }}" placeholder="Entre 1 et 5" type="number" />

                <flux:select wire:model="form.day_type" label="{{ __('Type d\'heures supp.') }}">
                    <flux:select.option value="">{{ __('Choisir une option') }}</flux:select.option>
                    @foreach(HsuppEnum::options() as $option)
                        <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="form.hours" label="{{ __('Nombre d\'heures supp.') }}" />
                <flux:input wire:model="form.hours_rate" label="{{ __('Taux horaire') }}" />
                <flux:textarea label="{{ __('Notes (Optionnel)') }}" wire:model="form.notes" />
            </div>

            <div class="flex justify-end items-center gap-2">
                <flux:button @click="activeForm = null">{{ __('Annuler') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Enregistrer') }}</flux:button>
            </div>
        </form>

        <flux:callout icon="information-circle" class="mt-5">
            <flux:callout.heading>{{ __('Information') }}</flux:callout.heading>
            <flux:callout.text>
                La base de calcul est égale au : (salaire catégoriel échelonné + diverses primes assimilées au salaire
                (prime de technicité, de rendement, de fonction)) × nombre d'heures × pourcentage des heures supplémentaires.
            </flux:callout.text>
        </flux:callout>
    </x-container>

    {{-- ─── FORM : IMPORT ─── --}}
    <x-container x-show="activeForm === 'b'" x-transition>
        <div class="flex justify-between items-center mb-5">
            <flux:heading level="1" size="lg">{{ __('Importer les heures supp.') }}</flux:heading>
            <flux:button wire:click="downloadTemplate" icon="arrow-down-tray">{{ __('Télécharger le template') }}</flux:button>
        </div>

        <form wire:submit="previewImport" class="space-y-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-2">
                <flux:button type="button" @click="activeForm = null">{{ __('Annuler') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Prévisualiser') }}</flux:button>
            </div>
        </form>

        @if(!empty($previewRows))
            <div class="mt-4">
                <flux:text>{{ __('Lignes prévisualisées') }}: {{ count($previewRows) }}</flux:text>
                @if(!empty($importErrors))
                    <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                        <flux:callout.heading>{{ __('Erreurs détectées') }}</flux:callout.heading>
                        <flux:callout.text>
                            @foreach($importErrors as $error)
                                <div>{{ __('Ligne') }} {{ $error['line'] }}: {{ implode(', ', $error['errors']) }}</div>
                            @endforeach
                        </flux:callout.text>
                    </flux:callout>
                @endif
                <div class="flex justify-end mt-3">
                    <flux:button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport">{{ __('Valider et importer') }}</flux:button>
                </div>
            </div>
        @endif
    </x-container>

    {{-- ─── DELTA CARDS (stats globales, sans pagination) ─── --}}
    @if($this->overtimeStats->isNotEmpty())
        <x-delta-card :cards="[
            [
                'label' => 'Total heures supp. ce mois',
                'current' => $this->overtimeStats->sum('hours').' h',
                'prev' => now()->format('M Y'),
                'delta' => '',
                'up' => true,
                'color' => 'blue',
            ],
            [
                'label' => 'Allocations estimées',
                'current' => number_format($this->overtimeStats->sum('alloc'), 0, ',', ' ').' F cfa',
                'prev' => 'Ajout au brut',
                'delta' => '',
                'up' => true,
                'color' => 'emerald',
            ],
            [
                'label' => 'Taux horaire',
                'current' => number_format($this->form->hours_rate, 0, ',', ' ').' F cfa',
                'prev' => '',
                'delta' => '',
                'up' => true,
                'color' => 'emerald',
            ],
        ]" />
    @endif

    {{-- ─── ARCHIVES / SNAPSHOTS ─── --}}
    <x-container x-show="activeForm === 'archives-overtimes'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">{{ __('Historique des heures supp. (snapshots)') }}</flux:heading>
                <flux:text>{{ __('Filtrez par ref (format m-Y).') }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :placeholder="__('ex: 05-2026')" />
                <flux:button wire:click="exportOvertimeArchives" icon="arrow-up-tray">{{ __('Exporter') }}</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Ref</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Semaine</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Type</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Heures</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Taux</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Alloc</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->overtimesSnapshot as $snapshot)
                    <tr wire:key="ot-snapshot-{{ $snapshot->id }}">
                        <td class="px-6 py-4 text-sm">{{ $snapshot->ref }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->week }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->day_type?->label() }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->hours }}</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($snapshot->hours_rate, 0, ',', ' ') }} F cfa</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($snapshot->alloc, 0, ',', ' ') }} F cfa</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8"><x-empty-state /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-container>

    {{-- ─── MAIN TABLE (Sheaf UI) ─── --}}
    <x-ui.table.container variant="default"                x-data="{ hiddenCols: $persist([]).as('overtimes-table-hidden-cols') }"    >

        {{-- Toolbar : bulk delete | search | column visibility --}}
        <div class="flex items-center gap-2">

            {{-- Bulk-delete : visible seulement quand des lignes sont sélectionnées --}}
            <div style="display:none;" wire:show="selectedIds.length">
                <flux:button
                    wire:click="deleteSelected"
                    wire:confirm="{{ __('Voulez-vous vraiment supprimer les heures supp. sélectionnées ? Cette action est irréversible.') }}"
                    variant="danger"
                    size="sm"
                    icon="trash"
                >
                    {{ __('Supprimer la sélection') }}
                    (<span x-text="$wire.selectedIds.length"></span>)
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="ml-auto">
                <flux:input
                    class="[&_input]:bg-transparent"
                    placeholder="{{ __('Rechercher...') }}"
                    leftIcon="magnifying-glass"
                    wire:model.live.debounce.300ms="searchQuery"
                />
            </div>

            {{-- Column visibility --}}
            <x-ui.dropdown checkbox checkboxVariant position="bottom-end">
                <x-slot:button>
                    <x-ui.button
                        icon="view-columns"
                        variant="soft"
                        size="sm"
                        class="rounded-box outline dark:outline-white/20 outline-neutral-900/10 dark:ring-white/15 ring-neutral-900/15 [[data-open]>&]:bg-white/5 [[data-open]>&]:ring-2 shadow-sm"
                        tooltip="{{ __('Colonnes visibles') }}"
                    />
                </x-slot:button>
                <x-slot:menu>
                    <x-ui.dropdown.item readOnly>{{ __('Colonnes masquées') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item value="hoursRate" x-model="hiddenCols">{{ __('Taux horaire') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.item value="addedBy" x-model="hiddenCols">{{ __('Ajouté par') }}</x-ui.dropdown.item>
                    <x-ui.dropdown.item value="notes" x-model="hiddenCols">{{ __('Notes') }}</x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>

        </div>

        {{-- Table --}}
        <x-ui.table
            wire:loading
            loadOn="pagination, search, sorting"
            id="table">
            <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white">
                <x-ui.table.columns withCheckAll>

                    {{-- Semaine — sortable --}}
                    <x-ui.table.head
                        column="week"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Semaine') }}
                    </x-ui.table.head>

                    {{-- Type — sortable --}}
                    <x-ui.table.head
                        column="day_type"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Type') }}
                    </x-ui.table.head>

                    {{-- Heures — sortable, dropdown --}}
                    <x-ui.table.head
                        column="hours"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Heures') }}
                    </x-ui.table.head>

                    {{-- Taux horaire (masquable) — sortable, dropdown --}}
                    <x-ui.table.head
                        column="hours_rate"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                        x-show="!hiddenCols.includes('hoursRate')"
                        x-cloak
                    >
                        {{ __('Taux horaire') }}
                    </x-ui.table.head>

                    {{-- Alloc estimés — sortable, dropdown --}}
                    <x-ui.table.head
                        column="alloc"
                        sortable
                        variant="dropdown"
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Alloc. estimés') }}
                    </x-ui.table.head>

                    {{-- Ajouté par (masquable) --}}
                    <x-ui.table.head x-show="!hiddenCols.includes('addedBy')" x-cloak>
                        {{ __('Ajouté par') }}
                    </x-ui.table.head>

                    {{-- Notes (masquable) --}}
                    <x-ui.table.head x-show="!hiddenCols.includes('notes')" x-cloak>
                        {{ __('Notes') }}
                    </x-ui.table.head>

                    {{-- Actions --}}
                    <x-ui.table.head>{{ __('Actions') }}</x-ui.table.head>

                </x-ui.table.columns>
            </x-ui.table.header>

            <x-ui.table.rows>
                @forelse($this->overtimes as $overtime)
                    <x-ui.table.row
                        :key="$overtime->id"
                        :checkboxId="$overtime->id"
                        class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
                    >
                        {{-- Semaine --}}
                        <x-ui.table.cell>
                            <span class="inline-flex items-center justify-center size-7 rounded-full bg-neutral-100 dark:bg-neutral-800 text-sm font-bold">
                                S{{ $overtime->week }}
                            </span>
                        </x-ui.table.cell>

                        {{-- Type --}}
                        <x-ui.table.cell>
                            <flux:heading class="font-medium">{{ $overtime->day_type->label() }}</flux:heading>
                        </x-ui.table.cell>

                        {{-- Heures --}}
                        <x-ui.table.cell>
                            <span class="font-semibold text-sm">{{ $overtime->hours }} h</span>
                        </x-ui.table.cell>

                        {{-- Taux horaire (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('hoursRate')" x-cloak>
                            <span class="font-mono text-sm">
                                {{ number_format($overtime->hours_rate, 0, ',', ' ') }} F cfa
                            </span>
                        </x-ui.table.cell>

                        {{-- Alloc estimés --}}
                        <x-ui.table.cell>
                            <span class="font-mono text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($overtime->alloc, 0, ',', ' ') }} F cfa
                            </span>
                        </x-ui.table.cell>

                        {{-- Ajouté par (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('addedBy')" x-cloak>
                            <span class="text-sm text-gray-500 dark:text-neutral-400">
                                {{ $overtime->added_by ?? '—' }}
                            </span>
                        </x-ui.table.cell>

                        {{-- Notes (masquable) --}}
                        <x-ui.table.cell x-show="!hiddenCols.includes('notes')" x-cloak>
                            @if($overtime->notes)
                                <flux:tooltip toggleable>
                                    <flux:button icon="information-circle" size="sm" variant="ghost" />
                                    <flux:tooltip.content>{{ $overtime->notes }}</flux:tooltip.content>
                                </flux:tooltip>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </x-ui.table.cell>

                        {{-- Actions --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $overtime->id }})" size="sm" variant="ghost" icon="pencil" tooltip="{{ __('Modifier') }}" />
                                <flux:button wire:click="confirmBeforeDelete({{ $overtime->id }})" size="sm" variant="ghost" icon="trash" tooltip="{{ __('Supprimer') }}" />
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
                                <h3 class="text-lg font-semibold">{{ __('Aucune heure supp. trouvée') }}</h3>
                                <p class="text-sm text-neutral-500">
                                    {{ __('Aucune heure supplémentaire enregistrée pour ').$employee->name.'.' }}
                                </p>
                            </x-ui.empty.contents>
                        </x-ui.empty>
                    </x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
        {{ $this->overtimes->links(data: ['scrollTo' => "#table" ]) }}

    </x-ui.table.container>

    {{-- ─── MODAL : EDIT ─── --}}
    <flux:modal name="edit-overtime-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">{{ __('Mettre à jour l\'heure supp.') }}</flux:heading>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-4 p-2">
                    <flux:input wire:model="form.week" label="{{ __('Numéro de la semaine') }}" placeholder="Entre 1 et 5" type="number" />

                    <flux:select wire:model="form.day_type" label="{{ __('Type d\'heures supp.') }}">
                        <flux:select.option value="">{{ __('Choisir une option') }}</flux:select.option>
                        @foreach(HsuppEnum::options() as $option)
                            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="form.hours" label="{{ __('Nombre d\'heures supp.') }}" />
                    <flux:input wire:model="form.hours_rate" label="{{ __('Taux horaire') }}" />
                    <flux:textarea label="{{ __('Notes (Optionnel)') }}" wire:model="form.notes" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button type="submit" variant="primary">{{ __('Enregistrer') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ─── MODAL : DELETE ─── --}}
    <flux:modal name="delete-overtime-modal">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">{{ __('Supprimer cette heure supp.') }}</flux:heading>
            @if($overtimeToDelete)
                <p>
                    Voulez-vous vraiment supprimer <strong>{{ $overtimeToDelete->day_type->label() }}</strong>
                    ajouté par <strong>{{ $overtimeToDelete->added_by }}</strong> ?
                </p>
                <p class="text-sm text-red-600 dark:text-red-400">{{ __('Cette action est irréversible.') }}</p>
            @endif
            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button>{{ __('Annuler') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">{{ __('Oui, j\'en suis sûr') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</div>