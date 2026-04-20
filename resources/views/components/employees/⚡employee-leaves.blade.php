<?php

use App\Enums\LeaveTypeEnum;
use App\Enums\StatusEnum;
use App\Livewire\Forms\EmployeeLeaveForm;
use App\Models\Leave;
use App\Services\CalculateDays;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new class extends Component
{
    use WithFileUploads;
    public $employee;
    public $showImportLeave = false;
    public $showAddLeaveForm = false;
    public $editingLeaveId;


    public $previewData = [];
    public $importErrors = [];
    public $importFile;
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

        $this->form->days = (new CalculateDays)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->create();

        $this->form->reset();
        $this->showAddLeaveForm = false;
        Flux::toast(variant: 'success', text: 'Votre absences ou congés a été ajouté avec succès.');
    }

    public function edit($leaveId)
    {
        $leaveToUpdate  = Leave::find($leaveId);
        $this->form->setLeave($leaveToUpdate);
        Flux::modal('edit-leave-modal')->show();
    }


    public function update()
    {
        $this->form->days = (new CalculateDays)->calculateDays($this->form->start_date, $this->form->end_date);
        $this->form->update();
        $this->form->reset();
        Flux::modal('edit-leave-modal')->close();
        Flux::toast(variant: 'success', text: 'Votre absence ou congé a été mis à jour avec succès.');
    }

    public $leaveToDelete = null;
    public function confirmBeforeDelete($idLeaveWeWantToDelete)
    {
        $this->leaveToDelete = Leave::find($idLeaveWeWantToDelete);
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

    public function downloadTemplate()
    {
        // Use the existing template file from public to avoid generating on the fly
        $publicPath = public_path('leaves_template.xlsx');

        if (!file_exists($publicPath)) {
            Flux::toast(variant: 'danger', text: "Template introuvable, Veuillez réessayer plus tard.");
            return;
        }
        return response()->download($publicPath, 'leaves_template.xlsx');
        Flux::toast(variant: 'success', text: 'Le téléchargement du template va démarrer...');
    }

    public function updatedImportFile()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls',
        ]);
        $this->previewData = [];
        $this->importErrors = [];
        $this->validationStep = false;

        $path = $this->importFile->getRealPath();
        $fastExcel = new FastExcel();
        $rows = $fastExcel->import($path);


        $rowIndex = 0;

        foreach ($rows as $row) {
            if (is_null($row) || (is_array($row) && empty(array_filter($row)))) {
                continue; // Skip empty rows
            }

            $rowIndex++;

            // Map column headers to expected fields
            $data = [
                'type' => $row['Type'] ?? $row['type'] ?? null,
                'days' => $row['Nbres jrs'],
                'status' => $row['Statut'],
                'approved_by' => $row['Approuvé par'],
                'approbation_date' => $row["Date approbation(AAAA/MM/JJ)"] ?? $row['approbation_date'] ?? null,
                'start_date' => $row['Date de début(AAAA/MM/JJ)'] ?? $row['start_date'] ?? null,
                'end_date' => $row['Date de fin(AAAA/MM/JJ)'] ?? $row['end_date'] ?? null,
                'notes' => $row['Notes'] ?? $row['notes'] ?? null,
            ];

            $rules = [
                'days' => ['required', 'numeric'],
                'approved_by' => ['required', 'string', 'max:100'],
                'approbation_date' => ['required', 'date'],
                'status' => ['required', 'string', Rule::in(StatusEnum::values())],
                'type' => ['required', 'string', Rule::in(LeaveTypeEnum::values())],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'notes' => ['nullable', 'string', 'max:100'],
            ];
            $validator = Validator::make($data, $rules);


            if ($validator->fails()) {
                $this->importErrors[$rowIndex] = [
                    'rowNumber' => $rowIndex,
                    'errors' => $validator->errors()->all(),
                    'data' => $data
                ];
            } else {
                $this->previewData[] = [
                    'rowNumber' => $rowIndex,
                    'data' => $row
                ];
            }
        }
    }
    public function confirmImport()
    {
        Gate::authorize('create', [Leave::class]);

        if (empty($this->previewData)) {
            Flux::toast(variant: 'danger', text: 'Aucune donnée valide à importer.');
            return;
        }

        // Extract only the data portion for the job
        $dataToImport = array_map(fn($item) => $item['data'], $this->previewData);

        //ImportLeavesJob::dispatch($dataToImport, $this->company_id);

        $this->resetImport();
        Flux::toast(variant: 'success', text: 'Importation en cours...');
    }

    public function proceedToValidation()
    {

        Gate::authorize('create', [Leave::class]);
        if (empty($this->previewData)) {
            Flux::toast(variant: 'danger', text: 'Aucune donnée valide à importer.');
            return;
        }

        foreach ($this->previewData as $item):

            $leaveData = [
                'employee_id' => $this->employee->id,
                'type' =>  $item['data']['Type'],
                'start_date' => $item['data']['Date de début(AAAA/MM/JJ)'],
                'end_date' => $item['data']['Date de fin(AAAA/MM/JJ)'],
                'days' => $item['data']['Nbres jrs'],
                'status' =>  $item['data']['Statut'],
                'notes' => $item['data']['Notes'],
                'approved_by' =>  $item['data']['Approuvé par'],
                'approbation_date' =>  $item['data']["Date approbation(AAAA/MM/JJ)"],
            ];

            $leave = $this->employee->leaves()->create($leaveData);

        /*if ($leave->type === LeaveTypeEnum::ANNUAL->value) {
                UpdateLeaveBalanceJob::dispatch($this->employee->id, $leave->days, $leave->end_date);
            }*/
        endforeach;

        $this->validationStep = true;
        $this->resetImport();
        Flux::toast(variant: 'success', text: __('Demande de congé et absence sont en cours d\' importation.'));
    }
    public function resetImport()
    {
        $this->importFile = null;
        $this->previewData = [];
        $this->importErrors = [];
        $this->validationStep = false;
    }
};
?>

<div>
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1" class="font-bold">{{ __('Gérer les congés et absences') }}</flux:heading>
            <flux:text class="text-gray-300">{{ __('Consultez le solde et enregistrez les absences de votre collaborateur.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button wire:click="toggleFormAddLeave" variant="primary">
                {{ __('Ajouter une absence') }}
            </flux:button>


            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>

                    <flux:menu.item wire:click="toggleImportLeave">
                        {{ __('Importer des absences et congés') }}
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    @if($showAddLeaveForm)
    <x-container wire:transition>
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

                <flux:button wire:click="toggleFormAddLeave">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Enregistrer') }}
                </flux:button>
            </div>
        </form>
    </x-container>
    @endif
    @if ($showImportLeave)


    <x-container wire:transition>
        <div class="flex align-items-center justify-between">
            <h3 class="font-semibold text-lg">Étape 1: Sélectionnez votre fichier</h3>
            <flux:button wire:click="downloadTemplate" variant="outline">
                Télécharger le modèle
            </flux:button>
        </div>
        <div class="space-y-3">
            <flux:text variant="subtle">Acceptés: Excel (.xlsx, .xls)</flux:text>
            <div class="flex items-center gap-2">

                <flux:input type="file" wire:model="importFile" label="Fichier Excel ou CSV" accept=".xlsx,.xls,.csv" required />
                <div wire:loading wire:target="importFile" class="flex items-center gap-2 text-sm text-zinc-600 mt-2">
                    <svg class="w-4 h-4 animate-spin text-zinc-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>Traitement du fichier…</span>
                </div>
            </div>
        </div>
    </x-container>
    @if ($importFile && (count($previewData) > 0 || count($importErrors) > 0))
    <x-container wire:transition>
        <h3 class="font-semibold text-lg">Étape 2: Aperçu des données</h3>

        @if (count($importErrors) > 0)
        <div class="border border-red-200 dark:border-red-800 rounded-xl p-4 bg-red-50 dark:bg-red-900/20">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <h4 class="font-semibold text-red-900 dark:text-red-100">
                    {{ count($importErrors) }} erreur(s) de validation
                </h4>
            </div>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @foreach ($importErrors as $error)
                <div class="border-l-4 border-red-600 pl-3 py-2">
                    <p class="font-medium text-red-800 dark:text-red-200">Ligne {{ $error['rowNumber'] }}</p>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside mt-1">
                        @foreach ($error['errors'] as $msg)
                        <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if (count($previewData) > 0)
        <div wire:transition>
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <h4 class="font-semibold text-green-900 dark:text-green-100">
                    {{ count($previewData) }} congé(s) valide(s)
                </h4>
            </div>

            <div class="overflow-x-auto rounded-lg border border-green-200 dark:border-green-800">
                <table class="w-full text-sm">
                    <thead class="bg-green-100 dark:bg-green-800/50 border-b border-green-200 dark:border-green-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">#</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Date début</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Date fin</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Nbres jrs</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Statut</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Approuvé par</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Approuvé le</th>
                            <th class="px-4 py-3 text-left font-semibold text-green-900 dark:text-green-100">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-200 dark:divide-green-800">
                        @foreach (array_slice($previewData, 0, 100) as $item)
                        <tr class="hover:bg-green-100/50 dark:hover:bg-green-900/20 transition">
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['rowNumber'] }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Type']}}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Date de début(AAAA/MM/JJ)']?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data'][ 'Date de fin(AAAA/MM/JJ)'] ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Nbres jrs']?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Statut']?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Approuvé par'] ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']["Date approbation(AAAA/MM/JJ)"] ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-green-900 dark:text-green-100">{{ $item['data']['Notes'] ?? 'N/A' }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if (count($previewData) > 100)
                <p class="mt-3 text-xs text-green-600 dark:text-green-400">
                    ... et {{ count($previewData) - 100 }} autre(s) enregistrement(s)
                </p>
                @endif
            </div>
        </div>
        <div class="flex justify-end items-center gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button wire:click="resetImport">
                Annuler l'import
            </flux:button>
            <div class="flex items-center gap-3">
                <flux:button wire:click="proceedToValidation" variant="primary" wire:loading.attr="disabled">
                    Valider l'import
                </flux:button>
                <span wire:loading wire:target="proceedToValidation" class="text-sm text-zinc-500">Préparation…</span>
            </div>
        </div>
        @endif
    </x-container>

    @endif
    @endif

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
                        {{ $leave->type->label() }}
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