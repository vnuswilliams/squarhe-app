<?php

use App\Concerns\HasTableOptions;
use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Jobs\ImportEmployeeLeavesJob;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Models\Leave;
use App\Services\CalculateDays;
use Carbon\Carbon;
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

new class extends Component{ 
use WithFileUploads;
use WithoutUrlPagination, WithPagination;
use HasTableOptions;

    public $employee;

    public $snapshotRef = "";

    public $importFile;

    public array $previewRows = [];

    public array $importErrors = [];

    public bool $readyToImport = false;

    public EmployeeLeaveForm $form;

    /**
     * Paginated + searchable + sortable leaves pour le tableau principal.
     */
    #[Computed]
    public function leaves()
    {
        $paginator = $this->baseQuery()
            ->when(filled($this->searchQuery), fn($q) => $this->applySearch($q))
            ->when(filled($this->sortBy), fn($q) => $this->applySorting($q))
            ->latest()
            ->paginate(10);

        // Requis par WithSelection pour "tout sélectionner sur la page"
        $this->visibleIds = $paginator->pluck("id")->map(fn($id) => (string) $id)->toArray();

        return $paginator;
    }

    /**
     * Toutes les leaves sans pagination — pour les delta cards.
     */
    #[Computed]
    public function leaveStats()
    {
        return $this->employee->leaves;
    }

    /**
     * Requête Eloquent de base partagée par toutes les méthodes.
     */
    protected function baseQuery()
    {
        return Leave::query()->where("employee_id", $this->employee->id);
    }

    /**
     * Sheaf WithSearch : colonnes interrogées.
     */
    protected function applySearch($query)
    {
        return $query->where(function ($q) {
            $q->where("notes", "like", "%" . $this->searchQuery . "%")
                ->orWhere("type", "like", "%" . $this->searchQuery . "%")
                ->orWhere("status", "like", "%" . $this->searchQuery . "%")
                ->orWhere("approved_by", "like", "%" . $this->searchQuery . "%");
        });
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->status = StatusEnum::APPROVED->value;
        $this->form->days = app(CalculateDays::class)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->create();
        $this->form->reset();
        Flux::toast(variant: "success", text: "Votre absence ou congé a été ajouté avec succès.");
    }

    public function edit($leaveId)
    {
        $leaveToUpdate = Leave::whereId($leaveId)->whereEmployeeId($this->employee->id)->firstOrFail();
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
        $this->leaveToDelete = Leave::whereId($idLeaveWeWantToDelete)->whereEmployeeId($this->employee->id)->first();

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

    /**
     * Suppression en masse des lignes sélectionnées.
     */
    public function deleteSelected()
    {
        Gate::authorize("delete", Leave::class);

        $this->baseQuery()->whereIn("id", $this->selectedIds)->delete();

        $this->deselectAll();
        Flux::toast(variant: "success", text: "Les absences/congés sélectionnés ont été supprimés.");
    }

    #[Computed]
    public function leavesSnapshot()
    {
        $query = $this->employee->leavesSnapshot;

        if (filled($this->snapshotRef)) {
            $query->where("ref", "like", "%" . trim($this->snapshotRef) . "%");
        }

        return $query ?? [];
    }

    public function exportLeavesArchives()
    {
        $rows = $this->leavesSnapshot->map(
            fn($snapshot) => [
                __("Ref") => $snapshot->ref,
                __("Type") => $snapshot->type?->label(),
                __("Date de début") => $snapshot->start_date,
                __("Date de fin") => $snapshot->end_date,
                __("Jours") => $snapshot->days,
                __("Statut") => $snapshot->status?->label(),
            ],
        );

        return new FastExcel($rows)->download("archives_conges_" . $this->employee->id . "_" . now()->format("m_Y") . ".xlsx");
    }

    public function previewImport(): void
    {
        $this->validate([
            "importFile" => ["required", "file", "mimes:xlsx,csv", "max:5120"],
        ]);

        $rows = new FastExcel()->import($this->importFile->getRealPath());
        $this->previewRows = [];
        $this->importErrors = [];

        foreach ($rows as $index => $row) {
            $data = [
                "type" => $row["type"] ?? null,
                "start_date" => $row["start_date"] ?? null,
                "end_date" => $row["end_date"] ?? null,
                "notes" => $row["notes"] ?? null,
                "last_leave" => $row["last_leave"] ?? null,
            ];

            $validator = Validator::make($data, [
                "type" => ["required", Rule::in(LeaveTypeEnum::values())],
                "start_date" => ["required", "date"],
                "end_date" => ["required", "date", "after_or_equal:start_date"],
                "notes" => ["nullable", "string", "max:100"],
                "last_leave" => ["nullable", "date"],
            ]);

            if ($validator->fails()) {
                $this->importErrors[] = ["line" => $index + 2, "errors" => $validator->errors()->all()];
            }

            $this->previewRows[] = $data;
        }

        $this->readyToImport = count($this->importErrors) === 0 && count($this->previewRows) > 0;
    }

    public function confirmImport(): void
    {
        if (!$this->readyToImport) {
            Flux::toast(variant: "danger", text: __("Corrigez les erreurs avant import."));
            return;
        }

        $path = $this->importFile->store("imports");
        ImportEmployeeLeavesJob::dispatch($path, $this->employee->id, auth()->user()->name);
        $this->reset("importFile", "previewRows", "importErrors", "readyToImport");
        Flux::toast(variant: "success", text: __("Import lancé. Le traitement est en cours."));
    }

    public function downloadTemplate()
    {
        $path = "templates/leaves_import_template.xlsx";

        if (!Storage::exists($path)) {
            $rows = collect([
                [
                    "type" => LeaveTypeEnum::ANNUAL->value,
                    "start_date" => now()->toDateString(),
                    "end_date" => now()->toDateString(),
                    "notes" => "Exemple",
                    "last_leave" => now()->subMonth()->toDateString(),
                ],
            ]);

            new FastExcel($rows)->export(Storage::path($path));
        }

        return Storage::download($path);
    }
};
?>

<div x-data="{ activeForm: null }">

    {{-- ─── PAGE HEADER ─── --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <flux:heading level="1" class="font-bold">{{ __("Gérer les congés et absences") }}</flux:heading>
            <flux:text class="text-gray-300">
                {{ __("Consultez le solde et enregistrez les absences de votre collaborateur.") }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button tooltip="Ajouter une absence ou un congé" @click="activeForm = activeForm === 'a' ? null : 'a'"
                variant="primary" icon="plus" />
            <flux:button tooltip="Voir les archives"
                @click="activeForm = activeForm === 'archives-leaves' ? null : 'archives-leaves'" icon="archive-box" />
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item @click="activeForm = 'b'">{{ __("Importer des absences et congés") }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="downloadTemplate">{{ __("Télécharger le template") }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- ─── FORM : ADD LEAVE ─── --}}
    <x-container x-show="activeForm === 'a'" x-transition>
        <flux:heading level="1" size="lg" class="mb-5">{{ __("Ajouter une absence ou un congé") }}
        </flux:heading>
        <form wire:submit="save">
            <div class="py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select label="{{ __('Type d\'absence') }}" wire:model.live="form.type">
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

                <flux:input wire:model="form.start_date" type="date" label="{{ __('Date de début') }}" />
                <flux:input wire:model="form.end_date" type="date" label="{{ __('Date de fin') }}" />
            </div>

            <div class="md:col-span-2">
                <flux:textarea label="{{ __('Notes') }}" wire:model="form.notes"
                    placeholder="{{ __('Motif du congé, détails...') }}" />
            </div>

            <div class="flex items-center justify-end gap-2 mt-4">
                <flux:button @click="activeForm = null">{{ __("Cancel") }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __("Enregistrer") }}</flux:button>
            </div>
        </form>
    </x-container>

    {{-- ─── FORM : IMPORT ─── --}}
    <x-container x-show="activeForm === 'b'" x-transition>
        <div class="flex justify-between items-center mb-5">
            <flux:heading level="1" size="lg">{{ __("Importer les absences") }}</flux:heading>
            <flux:button wire:click="downloadTemplate" icon="arrow-down-tray">{{ __("Télécharger le template") }}
            </flux:button>
        </div>

        <form wire:submit="previewImport" class="space-y-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-2">
                <flux:button type="button" @click="activeForm = null">{{ __("Cancel") }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __("Prévisualiser") }}</flux:button>
            </div>
        </form>

        @if (!empty($previewRows))
            <div class="mt-4">
                <flux:text>{{ __("Lignes prévisualisées") }}: {{ count($previewRows) }}</flux:text>
                @if (!empty($importErrors))
                    <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                        <flux:callout.heading>{{ __("Erreurs détectées") }}</flux:callout.heading>
                        <flux:callout.text>
                            @foreach ($importErrors as $error)
                                <div>{{ __("Ligne") }} {{ $error["line"] }}: {{ implode(", ", $error["errors"]) }}
                                </div>
                            @endforeach
                        </flux:callout.text>
                    </flux:callout>
                @endif
                <div class="flex justify-end mt-3">
                    <flux:button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport">
                        {{ __("Valider et importer") }}</flux:button>
                </div>
            </div>
        @endif
    </x-container>

    {{-- ─── DELTA CARDS (stats globales, sans pagination) ─── --}}
    <x-delta-card :cards='[
        [
            "label" => "Congés/absences pris ce mois",
            "current" => $this->leaveStats->sum("days") . " jrs",
            "delta" => "",
            "color" => "blue",
        ],
        [
            "label" => "Dernier congé (date de retour)",
            "current" =>
                $this->leaveStats->whereIn("type", [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])->first()
                    ?->last_leave ?? "Jamais en congé",
            "delta" => "",
            "color" => "emerald",
        ],
        [
            "label" => "Solde congé",
            "current" =>
                ($this->leaveStats->whereIn("type", [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])->first()
                    ?->leaves_balance ??
                    0) .
                " jrs",
            "delta" => "",
            "color" => "rose",
        ],
        [
            "label" => "Solde acquis ce mois",
            "current" =>
                $this->employee->data["leaves_majority"] +
                $this->employee->data["leaves_seniority"] +
                $this->employee->data["leaves_child"] .
                " jrs",
            "delta" => "",
            "color" => "rose",
        ],
    ]' />

    {{-- ─── ARCHIVES / SNAPSHOTS ─── --}}
    <x-container x-show="activeForm === 'archives-leaves'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">{{ __("Historique des congés/absences (snapshots)") }}</flux:heading>
                <flux:text>{{ __("Filtrez par ref (format m-Y).") }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :placeholder="__('Filtrer par ref 05-2026')" />
                <flux:button wire:click="exportLeavesArchives" icon="arrow-up-tray">{{ __("Exporter") }}</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Ref") }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Type") }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Date de début") }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Date de fin") }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Jours") }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __("Statut") }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse($this->leavesSnapshot as $snapshot)
                    <tr wire:key="leave-snapshot-{{ $snapshot->id }}">
                        <td class="px-6 py-4 text-sm">{{ $snapshot->ref }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->type?->label() }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->start_date }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->end_date }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->days }}</td>
                        <td class="px-6 py-4 text-sm">{{ $snapshot->status?->label() }}</td>
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
<x-ui.table.container variant="default" x-data="{ hiddenCols: $persist([]).as('leaves-table-hidden-cols') }">

        {{-- Toolbar : bulk delete | search | column visibility --}}
        <div class="flex items-center gap-2">

            {{-- Bulk-delete : visible seulement quand des lignes sont sélectionnées --}}
            <div style="display:none;" wire:show="selectedIds.length">
                <flux:button wire:click="deleteSelected"
                    wire:confirm="{{ __('Voulez-vous vraiment supprimer les absences/congés sélectionnés ? Cette action est irréversible.') }}"
                    variant="danger" size="sm" icon="trash">
                    {{ __("Supprimer la sélection") }}
                    (<span x-text="$wire.selectedIds.length"></span>)
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="ml-auto">
                <flux:input class="[&_input]:bg-transparent" placeholder="{{ __('Rechercher...') }}"
                    leftIcon="magnifying-glass" wire:model.live.debounce.300ms="searchQuery" />
            </div>

            {{-- Column visibility --}}
            <x-ui.dropdown checkbox checkboxVariant position="bottom-end">
                <x-slot:button>
                <flux:button icon="bars-3" />

                </x-slot:button>
                <x-slot:menu>
                    <x-ui.dropdown.item readOnly>{{ __("Colonnes masquées") }}</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item value="approvedBy"
                        x-model="hiddenCols">{{ __("Approuvé par") }}</x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>

        </div>

        {{-- Table --}}
        <x-ui.table  pagination:variant="full" wire:loading loadOn="pagination, search, sorting">
            <x-ui.table.header sticky class="dark:bg-neutral-900 bg-white" id="table">
                <x-ui.table.columns withCheckAll>

                    {{-- Type — sortable --}}
                    <x-ui.table.head column="type" sortable :currentSortBy="$sortBy" :currentSortDir="$sortDir">
                        {{ __("Type") }}
                    </x-ui.table.head>

                    {{-- Date début — sortable, dropdown --}}
                    <x-ui.table.head column="start_date" sortable variant="dropdown" :currentSortBy="$sortBy"
                        :currentSortDir="$sortDir">
                        {{ __("Date de début") }}
                    </x-ui.table.head>

                    {{-- Date fin — sortable, dropdown --}}
                    <x-ui.table.head column="end_date" sortable variant="dropdown" :currentSortBy="$sortBy"
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
                    <x-ui.table.head x-show="!hiddenCols.includes('approvedBy')" x-cloak>
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
                        <x-ui.table.cell x-show="!hiddenCols.includes('approvedBy')" x-cloak>
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
    {{-- ─── MODAL : EDIT ─── --}}

    <flux:modal name="edit-leave-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <flux:heading size="lg">{{ __("Mettre à jour un congé ou une absence") }}</flux:heading>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid sm:grid-cols-3 gap-4">
                    <flux:select label="{{ __('Type de congé') }}" wire:model="form.type">
                        <option>{{ __("Choisir un type") }}</option>
                        @foreach (LeaveTypeEnum::options() as $case)
                            <option value="{{ $case["value"] }}">{{ $case["label"] }}</option>
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

</div>
