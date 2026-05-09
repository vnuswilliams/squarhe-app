<?php

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Models\Leave;
use App\Jobs\ImportEmployeeLeavesJob;
use App\Services\CalculateDays;
use App\Services\DeterminateLeaveEmployeeQuotaService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Rap2hpoutre\FastExcel\Facades\FastExcel;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use WithFileUploads;
    public $employee;
    public $showImportLeave = false;
    public $showAddLeaveForm = false;
    public $showLeaveArchives = false;
    public $editingLeaveId;
    public $snapshotRef = '';


    public $previewData = [];
    public $importFile;
    public array $previewRows = [];
    public array $importErrors = [];
    public bool $readyToImport = false;
    public $validationStep = false;
    public EmployeeLeaveForm $form;
    public function mount($employee)
    {
        $this->employee = $employee;

         
    }
    #[Computed]
    public function leaves()
    {
        return $this->employee->leaves;
    }

    public function save()
    {
        $this->form->employee_id = $this->employee->id;
        $this->form->status = StatusEnum::APPROVED->value;

        $this->form->days = app(CalculateDays::class)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->create();

        $this->form->reset();
        $this->showAddLeaveForm = false;
        Flux::toast(variant: 'success', text: 'Votre absences ou congés a été ajouté avec succès.');
    }

    public function edit($leaveId)
    {
        $leaveToUpdate  = Leave::whereId($leaveId)  
        ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        $this->form->setLeave($leaveToUpdate);
        Flux::modal('edit-leave-modal')->show();
    }


    public function update()
    {
        $this->form->days = app(CalculateDays::class)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->update();
        $this->form->reset();
        Flux::modal('edit-leave-modal')->close();
        Flux::toast(variant: 'success', text: 'Votre absence ou congé a été mis à jour avec succès.');
    }

    public $leaveToDelete = null;
    public function confirmBeforeDelete($idLeaveWeWantToDelete)
    {
        $this->leaveToDelete = Leave::whereId($idLeaveWeWantToDelete)
            ->whereEmployeeId($this->employee->id)
            ->firstOrFail();
        Flux::modal('delete-leave-modal')->show();
    }
    public function delete()
    {
        if ($this->leaveToDelete):
            Gate::authorize('delete', [Leave::class, $this->leaveToDelete]);
            $this->leaveToDelete->delete();
            Flux::toast(variant: 'success', text: 'Votre absence ou congé a été supprimé avec succès.');
            Flux::modal('delete-leave-modal')->close();
            $this->leaveToDelete = null;
        endif;
    }


    #[Computed]
    public function leavesSnapshot()
    {
        $query = $this->employee->leavesSnapshot()->latest();

        if (filled($this->snapshotRef)) {
            $query->where('ref', 'like', '%' . trim($this->snapshotRef) . '%');
        }

        return $query->get();
    }

    public function exportLeavesArchives()
    {
        $rows = $this->leavesSnapshot->map(fn ($snapshot) => [
            __('Ref') => $snapshot->ref,
            __('Type') => $snapshot->type?->label(),
            __('Date de début') => $snapshot->start_date,
            __('Date de fin') => $snapshot->end_date,
            __('Jours') => $snapshot->days,
            __('Statut') => $snapshot->status?->label(),
        ]);

        return new FastExcel($rows)->download('archives_conges_' . $this->employee->id . '_' . now()->format('m_Y') . '.xlsx');
    }

    public function toggleFormAddLeave()
    {
        $this->showAddLeaveForm = !$this->showAddLeaveForm;
        $this->showImportLeave = false;
    }


    public function toggleImportLeave()
    {
        $this->showImportLeave = !$this->showImportLeave;
        $this->showAddLeaveForm = false;
    }

    public function previewImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        $rows = (new FastExcel())->import($this->importFile->getRealPath());
        $this->previewRows = [];
        $this->importErrors = [];

        foreach ($rows as $index => $row) {
            $data = [
                'type' => $row['type'] ?? null,
                'start_date' => $row['start_date'] ?? null,
                'end_date' => $row['end_date'] ?? null,
                'notes' => $row['notes'] ?? null,
                'last_leave' => $row['last_leave'] ?? null,
            ];

            $validator = Validator::make($data, [
                'type' => ['required', \Illuminate\Validation\Rule::in(LeaveTypeEnum::values())],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'notes' => ['nullable', 'string', 'max:100'],
                'last_leave' => ['nullable', 'date'],
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
            Flux::toast(variant: 'danger', text: __('Corrigez les erreurs avant import.'));
            return;
        }
        $path = $this->importFile->store('imports');
        ImportEmployeeLeavesJob::dispatch($path, $this->employee->id, auth()->id());
        $this->reset('importFile', 'previewRows', 'importErrors', 'readyToImport');
        Flux::toast(variant: 'success', text: __('Import lancé. Le traitement est en cours.'));
    }

    public function downloadTemplate()
    {
        $path = 'templates/leaves_import_template.xlsx';

        if (!Storage::exists($path)) {
            $rows = collect([[
                'type' => LeaveTypeEnum::ANNUAL->value,
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'notes' => 'Exemple',
                'last_leave' => now()->subMonth()->toDateString(),
            ]]);

            (new FastExcel($rows))->export(Storage::path($path));
        }

        return Storage::download($path);
    }

};
?>

<div x-data="{ activeForm : null }">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1" class="font-bold">{{ __('Gérer les congés et absences') }}</flux:heading>
            <flux:text class="text-gray-300">{{ __('Consultez le solde et enregistrez les absences de votre collaborateur.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button @click="activeForm = 'a' " variant="primary">
                {{ __('Ajouter une absence') }}
            </flux:button>
              


            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>

                    <flux:menu.item wire:click="toggleImportLeave">
                        {{ __('Importer des absences et congés') }}
                    </flux:menu.item>
                    <flux:menu.item wire:click="downloadTemplate">
                        {{ __('Télécharger le template') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <x-container x-show="activeForm === 'a' "  x-transition>
        <form wire:submit="save">
            <div class="py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select label="{{ __('Type d\'absence') }}" wire:model.live="form.type">
                    <option>{{ __('Choisir un type') }}</option>
                    @foreach (LeaveTypeEnum::options() as $case)
                    <option value="{{ $case['value'] }}">
                        {{ $case['label'] }}
                    </option>
                    @endforeach
                </flux:select>
                @if(in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                <flux:input wire:model="form.last_leave" type="date" label="Date du dernier congé annuel (optionnel) "></flux:input>
                @endif
                <flux:input wire:model="form.start_date" type="date" label="Date de début"></flux:input>
                <flux:input wire:model="form.end_date" type="date" label="Date de fin"></flux:input>
            </div>
            <div class="md:col-span-2">
                <flux:textarea label="{{ __('Notes') }}" wire:model="form.notes"
                    placeholder="{{ __('Motif du congé, détails...') }}"> </flux:textarea>
            </div>


            <div class="flex items-center justify-end gap-2 mt-4">

                <flux:button @click="activeForm = null " >
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Enregistrer') }}
                </flux:button>
            </div>
        </form>
            @if(!empty($previewRows))
                <div class="mt-4">
                    <flux:text>{{ __("Lignes prévisualisées") }}: {{ count($previewRows) }}</flux:text>
                    @if(!empty($importErrors))
                        <flux:callout icon="exclamation-triangle" variant="danger" class="mt-2">
                            <flux:callout.heading>{{ __("Erreurs détectées") }}</flux:callout.heading>
                            <flux:callout.text>
                                @foreach($importErrors as $error)
                                    <div>{{ __("Ligne") }} {{ $error["line"] }}: {{ implode(", ", $error["errors"]) }}</div>
                                @endforeach
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                    <div class="flex justify-end mt-3">
                        <flux:button wire:click="confirmImport" variant="primary" :disabled="!$readyToImport">{{ __("Valider et importer") }}</flux:button>
                    </div>
                </div>
            @endif

    </x-container>

    @if($showImportLeave)
    <x-container>
        <form wire:submit="previewImport" class="space-y-4 mt-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-2">
                <flux:button type="button" wire:click="toggleImportLeave">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Prévisualiser') }}</flux:button>
            </div>
        </form>
    </x-container>
    @endif

    @if($showImportLeave)
    <x-container>
        <form wire:submit="previewImport" class="space-y-4 mt-4">
            <flux:input type="file" wire:model="importFile" label="{{ __('Fichier Excel (xlsx/csv)') }}" />
            <div class="flex justify-end items-center gap-2">
                <flux:button type="button" wire:click="toggleImportLeave">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Prévisualiser') }}</flux:button>
            </div>
        </form>
    </x-container>
    @endif


    
    <x-delta-card :cards="[
            [
                'label' => 'Congés/absences pris ce mois',
                'current' => $this->leaves->sum('days').' jrs',
                'delta' => '',
                'color' => 'blue'
            ],
            [
                'label' => 'Dernier congé (date de retour) ',
                'current' =>  $this->leaves->whereIn('type', [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])
                ->first()?->last_leave ?? 'Jamais en congé',
                'delta' => '',
                'color' => 'emerald'
            ],
            [
                'label' => 'Solde congé',
                'current' =>  $this->leaves->whereIn('type', [LeaveTypeEnum::ANNUAL, LeaveTypeEnum::UNPAID])
                ->first()?->leaves_balance.' jrs' ?? '0',               
                'delta' => '',
                'color' => 'rose'
            ],            [
                'label' => 'Solde acquis ce                                          mois',
                'current' =>  $this->employee->data['leaves_majority'] + $this->employee->data['leaves_seniority'] + $this->employee->data['leaves_child'].' jrs' , 
                'delta' => '',
                'color' => 'rose'
            ],
           
        ]" />

    <div class="mt-4 mb-2 flex items-center gap-2">
        <flux:button @click="activeForm = activeForm === 'archives-leaves' ? null : 'archives-leaves'" variant="filled">
            {{ __('Afficher les archives') }}
        </flux:button>
    </div>

    <x-container x-show="activeForm === 'archives-leaves'" x-transition>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <flux:heading level="2">{{ __('Historique des congés/absences (snapshots)') }}</flux:heading>
                <flux:text>{{ __('Filtrez par ref (format m-Y).') }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="snapshotRef" :label="__('Filtrer par ref')" :placeholder="__('ex: 05-2026')" />
                <flux:button wire:click="exportLeavesArchives" icon="arrow-up-tray">{{ __('Exporter') }}</flux:button>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Ref') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Type') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Date de début') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Date de fin') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Jours') }}</th>
                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">{{ __('Statut') }}</th>
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
                    <td colspan="6" class="text-center py-8">{{ __('Aucune archive de congés trouvée.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-container>

    <x-container>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-700">
                <tr>

                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Type') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de début') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Date de fin') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Jours') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start cursor-pointer text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Statut') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase dark:text-neutral-400">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                @forelse ($this->leaves as $leave)
                <tr wire:key="{{ $leave->id }}">

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:heading class="flex items-center gap-2">
                            {{ $leave->type->label() }}
                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content>
                                    {{ $leave->notes }}
                                </flux:tooltip.content>
                            </flux:tooltip>

                        </flux:heading>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($leave->start_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $leave->days }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        <flux:badge color="{{ $leave->status->color() }}">{{ $leave->status->label() }}</flux:badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex items-center gap-2">
                            <flux:button wire:click="edit({{ $leave->id }})" size="sm" variant="ghost" icon="pencil" />


                            <flux:button wire:click="confirmBeforeDelete({{ $leave->id }})"
                                size="sm"
                                variant="ghost" icon="trash" />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <x-empty-state message=" 
                    {{ __('Aucune absences ou congés trouvés.') }}" />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </x-container>


    <flux:modal name="edit-leave-modal" class="min-w-225">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Mettre à jour un congé ou une absence</flux:heading>
            </div>
            <form wire:submit="update" class="container mx-auto p-4 max-w-4xl space-y-4">
                <div class="grid sm:grid-cols-3 gap-4">
                    <flux:select label="{{ __('Type de congé') }}" wire:model="form.type">
                        <option>{{ __('Choisir un type') }}</option>
                        @foreach (LeaveTypeEnum::options() as $case)
                        <option value="{{ $case['value'] }}">
                            {{ $case['label'] }}
                        </option>
                        @endforeach
                    </flux:select>

                    @if(in_array($form->type, [LeaveTypeEnum::ANNUAL->value]))
                    <flux:input wire:model="form.last_leave" type="date" label="Date du dernier congé annuel (optionnel) "></flux:input>
                    @endif
                    <flux:input type="date" label="Date de début" wire:model="form.start_date" />
                    <flux:input type="date" label="Date de fin" wire:model="form.end_date" />
                </div>
                <flux:textarea label="Notes" wire:model="form.notes" />


                <div class="flex justify-end gap-2  pt-4">
                    <flux:button type="submit" variant="primary">Enregistrer</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    <flux:modal name="delete-leave-modal">
        <div class="space-y-6 pt-5">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Supprimer ce congé ou absence</flux:heading>
            </div>
            @if($leaveToDelete)
            <p>
                Voulez vous vraiment supprimer {{$leaveToDelete->type->label()}} allant du {{ $leaveToDelete->start_date?->translatedFormat('d M Y') }} au {{ $leaveToDelete->end_date?->translatedFormat('d M Y') }} ?
            </p>
            <p>Cette action est irréversiblee.</p>
            @endif

            <div class="flex justify-end gap-2  pt-4">
                <flux:modal.close>
                    <flux:button>Annuler</flux:button>

                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Oui, j'en suis sûr</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
