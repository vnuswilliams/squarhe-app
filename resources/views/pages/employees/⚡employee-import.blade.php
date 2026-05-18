<?php

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\NationalityEnum;
use App\Jobs\ImportEmployeesJob;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Rap2hpoutre\FastExcel\FastExcel;

new #[Title('Importer des employés')] class extends Component
{
    use WithFileUploads;

    public $file = null;
    public string $tempPath     = '';
    public array  $preview      = [];
    public array  $allRows      = [];
    public array  $rowErrors    = [];   // [rowNumber => [errorMsg, ...]]
    public bool   $isValidated  = false;
    public bool   $hasErrors    = false;
    public int    $totalRows    = 0;
    public bool   $dispatched   = false;

    public array $columns = [
        'civility', 'name', 'email', 'phone', 'birth_date',
        'nationality', 'child', 'niu', 'cnps_number',
        'department', 'job_title', 'contract_type',
        'start_date', 'end_date', 'base_salary',
        'category', 'average_salary', 'smic',
    ];

    #[Computed]
    public function company()
    {
        return auth()->user()->company()->first();
    }

    // ──────────────────────────────────────────────
    //  Step 1 — File upload + instant preview
    // ──────────────────────────────────────────────

    public function updatedFile(): void
    {
        $this->resetImportState();

        $this->validateOnly('file', [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $this->tempPath = $this->file->store('employee-imports', 'local');

        try {
            $rows = (new FastExcel)->import(Storage::disk('local')->path($this->tempPath));

            $this->totalRows = $rows->count();
            $this->preview   = $rows->take(5)->values()->toArray();
            $this->allRows   = $rows->values()->toArray();
        } catch (\Throwable $e) {
            $this->addError('file', 'Fichier invalide ou mal formaté : ' . $e->getMessage());
            Storage::disk('local')->delete($this->tempPath);
            $this->tempPath = '';
        }
    }

    // ──────────────────────────────────────────────
    //  Step 2 — Full validation (all rows)
    // ──────────────────────────────────────────────

    public function validateImport(): void
    {
        $this->rowErrors  = [];
        $this->hasErrors  = false;

        if (empty($this->allRows)) {
            $this->addError('file', 'Aucune donnée à valider.');
            return;
        }

        // Collect emails / phones / nius / cnps within the file to detect intra-file duplicates
        $seenEmails = [];
        $seenPhones = [];
        $seenNius   = [];
        $seenCnps   = [];

        foreach ($this->allRows as $index => $row) {
            $rowNumber = $index + 2; // row 1 = header
            $errors    = [];

            // Intra-file duplicate checks
            $email = strtolower(trim($row['email'] ?? ''));
            $phone = trim($row['phone'] ?? '');
            $niu   = trim($row['niu'] ?? '');
            $cnps  = trim($row['cnps_number'] ?? '');

            if ($email && in_array($email, $seenEmails, true)) {
                $errors[] = "Email « {$email} » est dupliqué dans le fichier.";
            } else {
                $seenEmails[] = $email;
            }

            if ($phone && in_array($phone, $seenPhones, true)) {
                $errors[] = "Téléphone « {$phone} » est dupliqué dans le fichier.";
            } else {
                $seenPhones[] = $phone;
            }

            if ($niu && in_array($niu, $seenNius, true)) {
                $errors[] = "NIU « {$niu} » est dupliqué dans le fichier.";
            } else {
                $seenNius[] = $niu;
            }

            if ($cnps && in_array($cnps, $seenCnps, true)) {
                $errors[] = "CNPS « {$cnps} » est dupliqué dans le fichier.";
            } else {
                $seenCnps[] = $cnps;
            }

            // Laravel validation rules (DB uniqueness included)
            $validator = Validator::make($row, $this->rowRules(), $this->rowMessages());
            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            if (!empty($errors)) {
                $this->rowErrors[$rowNumber] = $errors;
                $this->hasErrors = true;
            }
        }

        $this->isValidated = true;
    }

    // ──────────────────────────────────────────────
    //  Step 3 — Dispatch job (only if no errors)
    // ──────────────────────────────────────────────

    public function import(): void
    {
        if (!$this->isValidated || $this->hasErrors) {
            Flux::toast(variant: 'danger', text: 'Veuillez corriger les erreurs avant d\'importer.');
            return;
        }

        ImportEmployeesJob::dispatch(
            $this->tempPath,
            $this->company->id,
            auth()->id(),
        );

        $this->dispatched = true;
        Flux::toast(
            variant: 'success',
            text: "{$this->totalRows} employé(s) en cours d'import — vous serez notifié(e) à la fin.",
        );
    }

    // ──────────────────────────────────────────────
    //  Template download
    // ──────────────────────────────────────────────

    public function downloadTemplate()
    {
        $columns = [
            'civility', 'name', 'email', 'phone', 'birth_date',
            'nationality', 'child', 'niu', 'cnps_number',
            'department', 'job_title', 'contract_type',
            'start_date', 'end_date', 'base_salary',
            'category', 'average_salary', 'smic',
        ];

        $example = [
            array_combine($columns, [
                'M', 'Jean Dupont', 'jean@example.com', '677000000',
                '1990-01-15', 'CM', '2', 'P123456789', 'CNPS123',
                'IT', 'Développeur', 'CDI',
                '2024-01-01', '', '150000',
                '3A', '', '',
            ]),
        ];

        return (new FastExcel(collect($example)))->download('modele-import-employes.xlsx');
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    private function resetImportState(): void
    {
        $this->preview     = [];
        $this->allRows     = [];
        $this->rowErrors   = [];
        $this->isValidated = false;
        $this->hasErrors   = false;
        $this->totalRows   = 0;
        $this->dispatched  = false;
    }

    private function rowRules(): array
    {
        return [
            'civility'      => ['required', Rule::in(CivilityEnum::values())],
            'name'          => ['required', 'string', 'max:50'],
            'email'         => ['required', 'email', 'max:30', 'unique:employees,email'],
            'phone'         => ['nullable', 'regex:/^[0-9]{9}$/', 'unique:employees,phone'],
            'birth_date'    => ['nullable', 'date'],
            'nationality'   => ['required', Rule::in(NationalityEnum::values())],
            'child'         => ['required', 'integer', 'min:0'],
            'niu'           => ['nullable', 'string', 'max:20', 'unique:employees,niu'],
            'cnps_number'   => ['nullable', 'string', 'max:255', 'unique:employees,cnps'],
            'department'    => ['required', 'string', 'max:30'],
            'job_title'     => ['required', 'string', 'max:50'],
            'contract_type' => ['required', Rule::in(ContractTypeEnum::values())],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['nullable', 'date', 'after:start_date'],
            'base_salary'   => ['required', 'numeric', 'min:0'],
            'category'      => ['nullable', 'max:3', 'regex:/^(?:[1-9]|1[0-2])(?:[A-Ga-g])?$/'],
            'average_salary'=> ['nullable', 'numeric', 'min:0'],
            'smic'          => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function rowMessages(): array
    {
        return [
            'civility.required'      => 'La civilité est obligatoire.',
            'civility.in'            => 'Civilité invalide (valeurs acceptées : ' . implode(', ', CivilityEnum::values()) . ').',
            'name.required'          => 'Le nom complet est obligatoire.',
            'name.max'               => 'Le nom ne doit pas dépasser 50 caractères.',
            'email.required'         => 'L\'email est obligatoire.',
            'email.email'            => 'Format d\'email invalide.',
            'email.unique'           => 'Cet email existe déjà en base.',
            'phone.regex'            => 'Le téléphone doit contenir exactement 9 chiffres.',
            'phone.unique'           => 'Ce numéro de téléphone existe déjà en base.',
            'nationality.required'   => 'La nationalité est obligatoire.',
            'nationality.in'         => 'Nationalité invalide.',
            'child.required'         => 'Le nombre d\'enfants est obligatoire.',
            'child.integer'          => 'Le nombre d\'enfants doit être un entier.',
            'niu.unique'             => 'Ce NIU existe déjà en base.',
            'cnps_number.unique'     => 'Ce numéro CNPS existe déjà en base.',
            'department.required'    => 'Le département est obligatoire.',
            'job_title.required'     => 'L\'intitulé du poste est obligatoire.',
            'contract_type.required' => 'Le type de contrat est obligatoire.',
            'contract_type.in'       => 'Type de contrat invalide (valeurs : ' . implode(', ', ContractTypeEnum::values()) . ').',
            'start_date.required'    => 'La date de début est obligatoire.',
            'start_date.date'        => 'Date de début invalide.',
            'end_date.after'         => 'La date de fin doit être postérieure à la date de début.',
            'base_salary.required'   => 'Le salaire de base est obligatoire.',
            'base_salary.numeric'    => 'Le salaire de base doit être numérique.',
            'base_salary.min'        => 'Le salaire de base doit être ≥ 0.',
            'category.regex'         => 'Catégorie invalide (ex : 1, 2A, 12G).',
        ];
    }
};
?>

<section class="w-full space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Importer des employés') }}</flux:heading>
            <flux:text variant="subtle">Importez plusieurs employés en une seule fois via un fichier Excel.</flux:text>
        </div>
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />
            <flux:breadcrumbs.item href="{{ route('employees') }}">Employés</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Import</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </div>

    @if(!$this->company)
        <x-no-company />
    @elseif($dispatched)

        {{-- ── Success state ───────────────────────────────────────── --}}
        <div class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md rounded-2xl border border-zinc-100 dark:border-zinc-800 p-10 flex flex-col items-center gap-4 text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                <flux:icon.check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
            </div>
            <flux:heading size="lg">Import lancé avec succès</flux:heading>
            <flux:text variant="subtle">
                {{ $totalRows }} employé(s) sont en cours de traitement en arrière-plan.
                Vous recevrez une notification une fois l'import terminé.
            </flux:text>
            <div class="flex gap-3 mt-2">
                <flux:button href="{{ route('employees') }}" variant="primary">
                    Voir les employés
                </flux:button>
                <flux:button wire:click="$set('dispatched', false)">
                    Nouvel import
                </flux:button>
            </div>
        </div>

    @else

        {{-- ── Step indicators ─────────────────────────────────────── --}}
        <div class="flex items-center gap-2 text-sm">
            <div @class([
                'flex items-center gap-1.5 font-medium',
                'text-primary-600 dark:text-primary-400' => !$tempPath,
                'text-green-600 dark:text-green-400'     => $tempPath,
            ])>
                <span @class([
                    'w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold',
                    'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' => !$tempPath,
                    'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300'         => $tempPath,
                ])>
                    @if($tempPath) ✓ @else 1 @endif
                </span>
                Fichier
            </div>

            <div class="h-px w-8 bg-zinc-300 dark:bg-zinc-700"></div>

            <div @class([
                'flex items-center gap-1.5 font-medium',
                'text-zinc-400 dark:text-zinc-600'       => !$tempPath && !$isValidated,
                'text-primary-600 dark:text-primary-400' => $tempPath && !$isValidated,
                'text-green-600 dark:text-green-400'     => $isValidated && !$hasErrors,
                'text-red-600 dark:text-red-400'         => $isValidated && $hasErrors,
            ])>
                <span @class([
                    'w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'                                     => !$tempPath && !$isValidated,
                    'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300'   => $tempPath && !$isValidated,
                    'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300'           => $isValidated && !$hasErrors,
                    'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300'                   => $isValidated && $hasErrors,
                ])>
                    @if($isValidated && !$hasErrors) ✓
                    @elseif($isValidated && $hasErrors) ✗
                    @else 2 @endif
                </span>
                Validation
            </div>

            <div class="h-px w-8 bg-zinc-300 dark:bg-zinc-700"></div>

            <div @class([
                'flex items-center gap-1.5 font-medium',
                'text-zinc-400 dark:text-zinc-600'       => !$isValidated || $hasErrors,
                'text-primary-600 dark:text-primary-400' => $isValidated && !$hasErrors,
            ])>
                <span @class([
                    'w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold',
                    'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'                                   => !$isValidated || $hasErrors,
                    'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300' => $isValidated && !$hasErrors,
                ])>3</span>
                Import
            </div>
        </div>

        {{-- ── Card 1 : Upload + template ──────────────────────────── --}}
        <div class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    1. Choisir le fichier
                </h2>
                <flux:button wire:click="downloadTemplate" size="sm" icon="arrow-down-tray">
                    Télécharger le modèle Excel
                </flux:button>
            </div>

            {{-- Dropzone --}}
            <div
    x-data="{
        dragging: false,

        handleDrop(event) {
            this.dragging = false

            const files = event.dataTransfer.files

            if (!files.length) return

            const input = this.$refs.fileInput

            input.files = files

            input.dispatchEvent(new Event('change', { bubbles: true }))
        }
    }"

    x-on:dragover.prevent="dragging = true"
    x-on:dragleave.prevent="dragging = false"
    x-on:drop.prevent="handleDrop($event)"

    :class="dragging
        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
        : 'border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40'"

    class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-10 text-center transition-colors cursor-pointer"
>

    <label for="file-upload" class="absolute inset-0 cursor-pointer"></label>

    <input
        x-ref="fileInput"
        id="file-upload"
        type="file"
        accept=".xlsx,.xls,.csv"
        wire:model="file"
        class="sr-only"
    />

    @if($tempPath)
        <flux:icon.document-check class="w-10 h-10 text-green-500" />

        <p class="font-medium text-gray-700 dark:text-gray-200">
            Fichier chargé —
            <span class="text-primary-600 dark:text-primary-400">
                {{ $totalRows }} ligne(s) détectée(s)
            </span>
        </p>

        <p class="text-sm text-zinc-500">
            Cliquez pour remplacer le fichier
        </p>
    @else
        <flux:icon.cloud-arrow-up class="w-10 h-10 text-zinc-400" />

        <p class="font-medium text-gray-700 dark:text-gray-200">
            Glissez-déposez votre fichier ici ou
            <span class="text-primary-600 dark:text-primary-400 underline underline-offset-2">
                cliquez pour parcourir
            </span>
        </p>

        <p class="text-sm text-zinc-500">
            Formats acceptés : .xlsx, .xls, .csv — Max 20 Mo
        </p>
    @endif

    <div wire:loading wire:target="file" class="flex items-center gap-2 text-sm text-zinc-500">
        <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>

        Lecture du fichier…
    </div>
</div>

            @error('file')
                <flux:callout variant="danger" icon="exclamation-triangle">
                    <flux:callout.heading>Fichier invalide</flux:callout.heading>
                    <flux:callout.text>{{ $message }}</flux:callout.text>
                </flux:callout>
            @enderror

            {{-- Column reference --}}
            <div class="rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 p-4 space-y-3">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide">
                    Colonnes attendues dans le fichier (ligne 1 = en-tête)
                </p>

                <div class="flex flex-wrap gap-1.5">
                    @foreach($columns as $col)
                        <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-mono
                            bg-white dark:bg-zinc-900 text-blue-700 dark:text-blue-300
                            border border-blue-200 dark:border-blue-700">
                            {{ $col }}
                        </span>
                    @endforeach
                </div>

                {{-- Enum values guide --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">

                    {{-- Civility --}}
                    <div class="rounded-lg bg-white dark:bg-zinc-900 border border-blue-100 dark:border-zinc-700 p-3 space-y-1.5">
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1">
                            <span class="font-mono text-blue-600 dark:text-blue-400">civility</span>
                            — valeurs acceptées
                        </p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(\App\Enums\CivilityEnum::cases() as $case)
                                <span class="inline-flex flex-col items-center rounded px-2 py-1 text-xs
                                    bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $case->value }}</span>
                                    <span class="text-zinc-400 text-[10px]">{{ $case->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Nationality --}}
                    <div class="rounded-lg bg-white dark:bg-zinc-900 border border-blue-100 dark:border-zinc-700 p-3 space-y-1.5">
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1">
                            <span class="font-mono text-blue-600 dark:text-blue-400">nationality</span>
                            — valeurs acceptées
                        </p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(\App\Enums\NationalityEnum::cases() as $case)
                                <span class="inline-flex flex-col items-center rounded px-2 py-1 text-xs
                                    bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $case->value }}</span>
                                    <span class="text-zinc-400 text-[10px]">{{ $case->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Contract type --}}
                    <div class="rounded-lg bg-white dark:bg-zinc-900 border border-blue-100 dark:border-zinc-700 p-3 space-y-1.5">
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1">
                            <span class="font-mono text-blue-600 dark:text-blue-400">contract_type</span>
                            — valeurs acceptées
                        </p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(\App\Enums\ContractTypeEnum::cases() as $case)
                                <span class="inline-flex flex-col items-center rounded px-2 py-1 text-xs
                                    bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $case->value }}</span>
                                    <span class="text-zinc-400 text-[10px]">{{ $case->label() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- Other field hints --}}
                <div class="flex flex-wrap gap-x-6 gap-y-1 pt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">phone</span> — 9 chiffres ex: <span class="font-mono">677000000</span></span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">birth_date / start_date / end_date</span> — format <span class="font-mono">YYYY-MM-DD</span></span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">child</span> — entier ≥ 0</span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">base_salary</span> — nombre ≥ 0</span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">category</span> — ex: <span class="font-mono">1, 2A, 12G</span> (optionnel)</span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">average_salary / smic</span> — optionnels, défaut = salaire de base</span>
                    <span><span class="font-mono text-zinc-700 dark:text-zinc-300">end_date / niu / cnps_number</span> — optionnels</span>
                </div>
            </div>
        </div>

        {{-- ── Card 2 : Preview ────────────────────────────────────── --}}
        @if(!empty($preview))
        <div class="bg-white/60 dark:bg-zinc-900/60 backdrop-blur-md p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 space-y-4">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                    Aperçu
                    <span class="text-sm font-normal text-zinc-500 ml-1">
                        (5 premières lignes sur {{ $totalRows }})
                    </span>
                </h2>
                <flux:button
                    wire:click="validateImport"
                    wire:loading.attr="disabled"
                    variant="primary"
                    icon="shield-check"
                    size="sm"
                >
                    <span wire:loading.remove wire:target="validateImport">Valider {{ $totalRows }} ligne(s)</span>
                    <span wire:loading wire:target="validateImport">Validation en cours…</span>
                </flux:button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full text-xs divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-zinc-500 dark:text-zinc-400">#</th>
                            @foreach($columns as $col)
                                <th class="px-3 py-2 text-left font-semibold text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                    {{ $col }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($preview as $i => $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-3 py-2 text-zinc-400 font-mono">{{ $i + 2 }}</td>
                                @foreach($columns as $col)
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300 max-w-[120px] truncate" title="{{ $row[$col] ?? '' }}">
                                        {{ $row[$col] ?? '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── Card 3 : Validation results ─────────────────────────── --}}
        @if($isValidated)
        <div @class([
            'backdrop-blur-md p-6 rounded-2xl border space-y-4',
            'bg-green-50/60 dark:bg-green-950/30 border-green-200 dark:border-green-800' => !$hasErrors,
            'bg-red-50/60 dark:bg-red-950/30 border-red-200 dark:border-red-800'         => $hasErrors,
        ])>
            <div class="flex items-start gap-3">
                @if(!$hasErrors)
                    <div class="shrink-0 w-9 h-9 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                        <flux:icon.check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">
                            Validation réussie — {{ $totalRows }} ligne(s) prête(s) à l'import
                        </h2>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-0.5">
                            Aucune erreur détectée. Cliquez sur « Lancer l'import » pour démarrer le traitement.
                        </p>
                    </div>
                @else
                    <div class="shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                        <flux:icon.x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-red-800 dark:text-red-200">
                            Import bloqué — {{ count($rowErrors) }} ligne(s) en erreur
                        </h2>
                        <p class="text-sm text-red-700 dark:text-red-400 mt-0.5">
                            Corrigez toutes les erreurs dans le fichier puis re-déposez-le pour relancer la validation.
                        </p>
                    </div>
                @endif
            </div>

            @if($hasErrors)
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach($rowErrors as $rowNumber => $errors)
                    <div class="rounded-lg bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-800/60 p-3">
                        <p class="text-xs font-bold text-red-700 dark:text-red-400 mb-1">
                            Ligne {{ $rowNumber }}
                        </p>
                        <ul class="space-y-0.5 list-disc list-inside">
                            @foreach($errors as $error)
                                <li class="text-xs text-red-600 dark:text-red-400">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- ── Actions ──────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-4">
            <flux:button href="{{ route('employees') }}" variant="ghost">
                Annuler
            </flux:button>

            <div class="flex items-center gap-3">
                @if($tempPath && !$dispatched)
                    <flux:button
                        wire:click="validateImport"
                        wire:loading.attr="disabled"
                        :disabled="empty($preview)"
                        variant="filled"
                        icon="shield-check"
                    >
                        <span wire:loading.remove wire:target="validateImport">Valider le fichier</span>
                        <span wire:loading wire:target="validateImport">Validation…</span>
                    </flux:button>
                @endif

                @if($isValidated && !$hasErrors)
                    <flux:button
                        wire:click="import"
                        wire:confirm="Confirmer l'import de {{ $totalRows }} employé(s) ?"
                        variant="primary"
                        icon="arrow-up-tray"
                    >
                        Lancer l'import ({{ $totalRows }})
                    </flux:button>
                @endif
            </div>
        </div>

    @endif
</section>