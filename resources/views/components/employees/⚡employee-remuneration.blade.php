<?php

use App\Concerns\HasTableOptions;
use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\PermissionEnum;
use App\Enums\RemunerationEnum;
use App\Jobs\ImportEmployeeRemunerationsJob;
use App\Livewire\Forms\EmployeeRemunerationForm;
use App\Models\Remuneration;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use HasTableOptions;
    use WithFileUploads;
    use WithoutUrlPagination, WithPagination;

    public $employee;

    public $importFile;

    public array $previewRows = [];

    public array $importErrors = [];

    public bool $readyToImport = false;

    public EmployeeRemunerationForm $form;

    public function mount()
    {
        $this->avgSalary = $this->employee->data['average_salary'] ?? 0;
        $this->smic = $this->employee->data['smic'] ?? 0;
    }

    /**
     * Paginated + searchable + sortable remunerations for the main table.
     */
    #[Computed]
    public function remunerations()
    {
        $paginator = $this->baseQuery()
            ->when(filled($this->searchQuery), fn ($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn ($q) => $this->applySorting($q))
            ->latest()
            ->paginate(10);

        // Required by WithSelection so "select all on page" works correctly.
        $this->visibleIds = $paginator->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        return $paginator;
    }

    /**
     * Aggregate stats across ALL remunerations (no pagination) — used by the delta cards.
     */
    #[Computed]
    public function remunerationStats()
    {
        return $this->employee->remunerations;
    }

    /**
     * Core Eloquent query shared by all methods.
     */
    protected function baseQuery()
    {
        return Remuneration::whereEmployeeId($this->employee->id);
    }

    /**
     * Sheaf WithSearch: define which columns are searched.
     */
    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where('name', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('notes', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('added_by', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('amount', 'like', '%'.$this->searchQuery.'%');
        });
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->type = RemunerationEnum::from($this->form->name)->type();

        $this->form->create();
        Flux::toast(variant: 'success', text: __('toast.remun.addElem'));
        $this->form->reset();
    }

    public function edit($remunId)
    {
        $remunToUpdate = Remuneration::whereId($remunId)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        $this->form->setRemun($remunToUpdate);
        Flux::modal('edit-remuneration-modal')->show();
    }

    public function update()
    {
        $this->form->update();
        $this->form->reset();
        Flux::modal('edit-remuneration-modal')->close();
        Flux::toast(variant: 'success', text: __('toast.remun.updateElem'));
    }

    public $remunerationToDelete = null;

    public function confirmBeforeDelete($idRemunWeWantToDelete)
    {
        $this->remunerationToDelete = Remuneration::whereId($idRemunWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->first();

        if ($this->remunerationToDelete) {
            Flux::modal('delete-remuneration-modal')->show();

            return;
        }

        Flux::toast(variant: 'warning', text: __('toast.deleteNotFound'));
    }

    public function delete()
    {
        if ($this->remunerationToDelete) {
            Gate::authorize('delete', [Remuneration::class, $this->remunerationToDelete]);
            $this->remunerationToDelete->delete();
            Flux::toast(variant: 'success', text: __('toast.remun.deleteElem'));
            Flux::modal('delete-remuneration-modal')->close();
            $this->remunerationToDelete = null;
        }
    }

    /**
     * Bulk-delete all selected rows.
     */
    public function deleteSelected()
    {
        // Gate::authorize('delete', Remuneration::class);

        // eauth()->user()->can(PermissionEnum::DELETE_REMUNERATION->ownerPermission());

        $this->baseQuery()
            ->whereIn('id', $this->selectedIds)
            ->delete();

        $this->deselectAll();
        Flux::toast(variant: 'success', text: __('toast.remun.deleteElem'));
    }

    public $avgSalary;

    public $smic;

    public $snapshotRef = '';

    #[Computed]
    public function remunerationsSnapshot()
    {
        $query = $this->employee->remunerationsSnapshot;

        if (filled($this->snapshotRef)) {
            $query->where('ref', 'like', '%'.trim($this->snapshotRef).'%');
        }

        return $query ?? [];
    }

    public function exportRemunerationArchives()
    {
        $rows = $this->remunerationsSnapshot->map(fn ($snapshot) => [
            __('Ref') => $snapshot->ref,
            __('Nom') => $snapshot->name?->label(),
            __('Type') => $snapshot->type?->label(),
            __('Montant') => $snapshot->amount,
            __('Périodicité') => $snapshot->periodicity?->label(),
            __('Impact') => $snapshot->impact?->label(),
        ]);

        return (new FastExcel($rows))->download('archives_remunerations_'.$this->employee->id.'_'.now()->format('m_Y').'.xlsx');
    }

    public function addAvgSalary()
    {
        $data = $this->employee->data;

        $this->validate([
            'avgSalary' => 'nullable|numeric|min:1',
            'smic' => 'nullable|numeric|min:1',
        ]);

        $data['smic'] = $this->avgSalary;
        $data['average_salary'] = $this->smic;

        $this->employee->update(['data' => $data]);

        Flux::toast(variant: 'success', text: __('toast.remun.avgSuccess'));
    }
   

    public function exportSelected()
    {
        Flux::toast(variant: 'warning', text : 'Fonctionnalité disponible très prochainement');

        return;
        $export = $this->baseQuery();
        if (filled($this->selectedIds)) {
            $export = $export->whereIn('id', $this->selectedIds);

        }

        return $this->csv($export->get());
    }
};
?>

<div x-data="{ activeForm: null }">

    {{-- ─── PAGE HEADER ─── --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <flux:heading level="1" class="font-bold">Éléments de rémunération</flux:heading>
            <flux:text class="text-gray-300">Primes, retenues, et autres variables de paie appliqués à cet employé.</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button @click="activeForm = activeForm === 'a' ? null : 'a'" variant="primary" icon="plus" tooltip="Ajouter un élément de rémunération" />
            <flux:button @click="activeForm = activeForm === 'archives-remunerations' ? null : 'archives-remunerations'" tooltip="Voir les archives" icon="archive-box" />
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item @click="activeForm = 'b'">{{ __('Add average salary') }}</flux:menu.item>
                    <flux:menu.item href="{{ route('employees.import.remunerations') }}">
{{ __('Importer des éléments de rémun') }}
</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- ─── FORM : ADD ELEMENT ─── --}}
    <x-container x-show="activeForm === 'a'" x-transition>
        <flux:heading level="1" size="lg" class="mb-5">Ajouter des éléments de rémunération de votre employé</flux:heading>
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <flux:select label="Nom de l'élément" wire:model="form.name">
                        <flux:select.option value="">Choisir un élément</flux:select.option>
                        @foreach(RemunerationEnum::forSelect() as $option)
                            <flux:select.option value="{{ $option->value }}">{{ $option->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input label="Montant" placeholder="Montant de l'élément" wire:model="form.amount" />
                </div>
                <div class="space-y-4">
                    <flux:select label="Périodicité" wire:model="form.periodicity">
                        <flux:select.option value="">Choisir</flux:select.option>
                        @foreach(PeriodicityEnum::options() as $option)
                            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select label="Impact" wire:model="form.impact">
                        <flux:select.option value="">Choisir</flux:select.option>
                        @foreach(ImpactEnum::options() as $option)
                            <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>
                </div>
            </div>
            <div class="flex justify-end items-center mt-5 gap-4">
                <flux:button type="button" @click="activeForm = null">Annuler</flux:button>
                <flux:button type="submit" variant="primary">Enregistrer</flux:button>
            </div>
        </form>
    </x-container>

    {{-- ─── FORM : IMPORT ─── --}}
    <x-container x-show="activeForm === 'c'" x-transition>
        <div class="flex justify-between items-center">
            <flux:heading level="1" size="lg" class="mb-5">{{ __('Importer les éléments de rémunération') }}</flux:heading>
            <flux:button wire:click="downloadTemplate" icon="arrow-down-tray">{{ __('Télécharger le template') }}</flux:button>
        </div>
        <form wire:submit="previewImport" class="space-y-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-4">
                <flux:button type="button" @click="activeForm = null">{{ __('Cancel') }}</flux:button>
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

    {{-- ─── FORM : AVG SALARY ─── --}}
    <x-container x-show="activeForm === 'b'" x-transition>
        <flux:heading level="1" size="lg" class="mb-5">Ajouter le salaire moyen et le smic de {{ $employee->name }}</flux:heading>
        <form wire:submit="addAvgSalary" class="">
            <flux:input wire:model="avgSalary" label="Salaire moyen" />
            <flux:input wire:model="smic" label="SMIC du secteur" />
            <flux:callout class="m-4" icon="information-circle">
                <flux:callout.heading>Information</flux:callout.heading>
                <flux:callout.text>
                    <ul>
                        <li>Salaire moyen : il sert à calculer les allocations congés annuel payé de votre employé.</li>
                        <li>SMIC du secteur : il sert à calculer la prime d'ancienneté.</li>
                    </ul>
                    <flux:text class="text-bold">Si non fourni le salaire de base sera utilisé comme base de calcul.</flux:text>
                </flux:callout.text>
            </flux:callout>
            <div class="flex justify-end items-center gap-4">
                <flux:button @click="activeForm = null">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">Ajouter</flux:button>
            </div>
        </form>
    </x-container>

    {{-- ─── DELTA CARDS (stats sur toutes les rémuné., sans pagination) ─── --}}
    @if($this->remunerationStats->isNotEmpty())
        <x-delta-card :cards="[
            [
                'label' => 'Total éléments de rémunération',
                'current' => $this->remunerationStats->sum('amount').' F cfa',
                'delta' => '',
                'color' => 'blue'
            ],
            [
                'label' => 'Eléments côtisable',
                'current' => ($this->remunerationStats->where('impact', ImpactEnum::TAXCOT)->sum('amount') +
                    $this->remunerationStats->where('impact', ImpactEnum::COTISABLE)->sum('amount')).' F cfa',
                'delta' => '',
                'color' => 'emerald'
            ],
            [
                'label' => 'Eléments taxable',
                'current' => ($this->remunerationStats->where('impact', ImpactEnum::TAXCOT)->sum('amount') +
                    $this->remunerationStats->where('impact', ImpactEnum::TAXABLE)->sum('amount')).' F cfa',
                'delta' => '',
                'color' => 'rose'
            ],
            [
                'label' => 'Eléments neutres',
                'current' => $this->remunerationStats->where('impact', ImpactEnum::NEUTRE)->sum('amount').' F cfa',
                'delta' => '',
                'color' => 'rose'
            ],
        ]" />
    @endif

    {{-- ─── ARCHIVES / SNAPSHOTS ─── --}}
    <x-container x-show="activeForm === 'archives-remunerations'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">Historique des rémunérations (snapshots)</flux:heading>
                <flux:text>Filtrez par ref (format m-Y).</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :placeholder="__('Filtrer par ref 05-2026')" />
                <flux:button wire:click="exportRemunerationArchives" icon="arrow-up-tray">{{ __('Exporter') }}</flux:button>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Ref</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Nom</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Type</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Montant</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Périodicité</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">Impact</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->remunerationsSnapshot as $snapshot)
                    <tr wire:key="remu-snapshot-{{ $snapshot->id }}">
                        <td class="px-6 py-4 text-sm">{{ $snapshot->ref }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->name?->label() }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->type?->label() }}</td>
                        <td class="px-6 py-4 text-sm">{{ number_format($snapshot->amount, 0, ',', ' ') }} F cfa</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->periodicity?->label() }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->impact?->label() }}</td>
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
    <x-container>
        {{-- Toolbar : bulk actions | search | column visibility --}}
        <div class="flex items-center gap-2">

            {{-- Bulk-delete : visible seulement quand des lignes sont sélectionnées --}}
            <div style="display:none;" wire:show="selectedIds.length">
                <flux:button
                    wire:click="deleteSelected"
                    wire:confirm="{{ __('Voulez-vous vraiment supprimer les éléments sélectionnés ? Cette action est irréversible.') }}"
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
                    placeholder="{{ __('Rechercher...') }}"
                    wire:model.live.debounce.300ms="searchQuery"
                />
                <flux:button  wire:click="exportSelected" >Exporter</flux:button>
            </div>          

        </div>

    <x-ui.table.container>


        {{-- Table --}}
        <x-ui.table       variant="default"         wire:loading            loadOn="pagination, search, sorting"        >
            <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="table">
                <x-ui.table.columns withCheckAll>

                    {{-- Nom —sortable --}}
                    <x-ui.table.head         variant="default"                column="name"                        sortable                        :currentSortBy="$sortBy"                        :currentSortDir="$sortDir"                    >
                        {{ __('Nom') }}
                    </x-ui.table.head>

                    {{-- Type --}}
                    <x-ui.table.head      variant="default"                   column="type"                        sortable                        :currentSortBy="$sortBy"                       :currentSortDir="$sortDir"                    >
                        {{ __('Type') }}
                    </x-ui.table.head>

                    {{-- Montant — sortable, dropdown variant --}}
                    <x-ui.table.head  variant="default"
                        column="amount"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Montant') }}
                    </x-ui.table.head>

                    {{-- Périodicité --}}
                    <x-ui.table.head  variant="default"
                        column="periodicity"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Périodicité') }}
                    </x-ui.table.head>

                    {{-- Impact --}}
                    <x-ui.table.head
                        column="impact"
                        sortable
                        :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir"
                    >
                        {{ __('Impact') }}
                    </x-ui.table.head>

                    {{-- Ajouté par (masquable) --}}
                    <x-ui.table.head>
                        {{ __('Ajouté par') }}
                    </x-ui.table.head>

                    {{-- Actions --}}
                    <x-ui.table.head>{{ __('Actions') }}</x-ui.table.head>

                </x-ui.table.columns>
            </x-ui.table.header>

            <x-ui.table.rows>
                @forelse($this->remunerations as $remun)
                    <x-ui.table.row
                        :key="$remun->id"
                        :checkboxId="$remun->id"
                        class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
                    >
                        {{-- Nom --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-1">
                                <flux:heading class="font-medium">
                                    {{ $remun->name->label() }}
                                </flux:heading>


                                @if($remun->notes)
                                <flux:button icon="information-circle" variant="ghost" tooltip="{{ $remun->notes }}" />
                                @endif
                            </div>
                        </x-ui.table.cell>

                        {{-- Type --}}
                        <x-ui.table.cell>
                            <span class="text-sm text-gray-600 dark:text-neutral-300">{{ $remun->type->label() }}</span>
                        </x-ui.table.cell>

                        {{-- Montant --}}
                        <x-ui.table.cell>
                            <span class="font-mono text-sm font-semibold">
                                {{ number_format($remun->amount, 0, ',', ' ') }} F cfa
                            </span>
                        </x-ui.table.cell>

                        {{-- Périodicité --}}
                        <x-ui.table.cell>
                            <span class="text-sm">{{ $remun->periodicity->label() }}</span>
                        </x-ui.table.cell>

                        {{-- Impact --}}
                        <x-ui.table.cell>
                         
                                {{ $remun->impact->label() }}
                        </x-ui.table.cell>

                        {{-- Ajouté par (masquable) --}}
                        <x-ui.table.cell >
                            <span class="text-sm text-gray-500 dark:text-neutral-400">{{ $remun->added_by }}</span>
                        </x-ui.table.cell>
                        {{-- Actions --}}
                        <x-ui.table.cell>
                            <div class="flex items-center gap-2">
                                <flux:button wire:click="edit({{ $remun->id }})" square icon="pencil" tooltip="{{ __('Modifier') }}" />
                                <flux:button wire:click="confirmBeforeDelete({{ $remun->id }})" square icon="trash" tooltip="{{ __('Supprimer') }}" />
                            </div>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>
                    <x-empty-state message=" {{ __('Aucun rémunérations trouvés pour ').$this->employee->shortName }}" />
                    </x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>
        {{ $this->remunerations->links(data: ['scrollTo' => "#table" ]) }}

    </x-ui.table.container>
    </x-container>

    {{-- ─── MODAL : EDIT ─── --}}
    <flux:modal name="edit-remuneration-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">Mettre à jour un élément de rémunération</flux:heading>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <flux:select label="Nom de l'élément" wire:model="form.name">
                            <flux:select.option value="">Choisir un élément</flux:select.option>
                            @foreach(RemunerationEnum::forSelect() as $option)
                                <flux:select.option value="{{ $option->value }}">{{ $option->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input label="Montant" placeholder="Montant de l'élément" wire:model="form.amount" />
                    </div>
                    <div class="space-y-4">
                        <flux:select label="Périodicité" wire:model="form.periodicity">
                            <flux:select.option value="">Choisir</flux:select.option>
                            @foreach(PeriodicityEnum::options() as $option)
                                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select label="Impact" wire:model="form.impact">
                            <flux:select.option value="">Choisir</flux:select.option>
                            @foreach(ImpactEnum::options() as $option)
                                <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:textarea label="Notes (Optionnel)" wire:model="form.notes"></flux:textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- ─── MODAL : DELETE ─── --}}
    <flux:modal name="delete-remuneration-modal">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">Supprimer cet élément de rémunération</flux:heading>
            @if($remunerationToDelete)
                <p>
                    Voulez-vous vraiment supprimer <strong>{{ $remunerationToDelete->name->label() }}</strong> ajouté par <strong>{{ $remunerationToDelete->added_by }}</strong> ?
                </p>
                <p class="text-sm text-red-600 dark:text-red-400">Cette action est irréversible.</p>
            @endif
            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>

</div>