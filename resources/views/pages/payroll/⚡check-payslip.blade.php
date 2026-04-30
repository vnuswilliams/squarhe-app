<?php

use App\Enums\StatusEnum;
use App\Jobs\CalculatePayslipJob;
use App\Jobs\ProcessPayslipDocumentJob;
use App\Models\Employee;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Valider les bulletins')] class extends Component
{
    // ── État sérialisé par Livewire ──────────────────────────────────────────
    public string $search = '';

    public string $activeTab = 'pending';

    public ?string $selectedEmployeeId = null;

    public bool $validatePayslip = false;

    public bool $readyToLoad = false;

    public array $dispatchedJobs = [];


    // ────────────────────────────────────────────────────────────────────────
    //  Lifecycle
    // ────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
               $this->readyToLoad = true;

        // 2 requêtes max pour l'auto-sélection — aucune N+1
        $firstId = $this->baseQuery()->needsPayslip()->value('id')
            ?? $this->baseQuery()->value('id');

        if ($firstId) {
            $this->selectEmployee($firstId);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Query helper — source unique de vérité
    // ────────────────────────────────────────────────────────────────────────

    private function baseQuery(): Builder
    {
        return Employee::query()
            ->active()
            ->notInternship()
            ->when(
                filled($this->search),
                fn (Builder $q) => $q->where('name', 'like', "%{$this->search}%")
            );
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Computed — réévalués chaque render, JAMAIS sérialisés → 0 N+1
    // ────────────────────────────────────────────────────────────────────────

    /**
     * ✅ FIX N+1 : l'employé est toujours rechargé fresh avec toutes ses
     *    relations. Livewire ne sérialise plus un modèle sans relations.
     */
    #[Computed]
    public function employee(): ?Employee
    {
        if (! $this->selectedEmployeeId) {
            return null;
        }

        return Employee::with([
            'payslip',
        ])->find($this->selectedEmployeeId);
    }

    /** ✅ Search intégré — colonnes minimales pour la liste */
    #[Computed]
    public function pendingEmployees()
    {
        return $this->baseQuery()
            ->needsPayslip()
            ->with(['payslip:id,employee_id,status'])
            ->get(['id', 'name', 'job_title']);
    }

    #[Computed]
    public function validatedEmployees()
    {
        return $this->baseQuery()
            ->withPayslipStatus(StatusEnum::APPROVED)
            ->with(['payslip:id,employee_id,status'])
            ->get(['id', 'name', 'job_title']);
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Actions
    // ────────────────────────────────────────────────────────────────────────

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    /**
     * ✅ FIX PRINCIPAL : $employeeId est string (UUID).
     *    On change l'ID → le computed employee() recharge automatiquement.
     */
    public function selectEmployee(string $employeeId): void
    {
        $this->selectedEmployeeId = $employeeId;
        unset($this->employee); // invalide le cache computed

        if (! $this->employee) {
            return;
        }

        if (! $this->employee->payslip) {
            CalculatePayslipJob::dispatch($this->employee);
        }

        $this->validatePayslip = $this->employee->payslip?->status === StatusEnum::APPROVED;
    }

    public function refreshEmployee(): void
    {
        unset($this->employee);
        Flux::toast(variant: 'info', text: 'Bulletin en cours de génération...');
    }

    public function checkPayslip(): void
    {
        if (! $this->selectedEmployeeId) {
            return;
        }

        unset($this->employee); // force rechargement depuis DB

        if (! $this->employee) {
            return;
        }

        if (! $this->employee->payslip) {
            if (! isset($this->dispatchedJobs[$this->employee->id])) {
                CalculatePayslipJob::dispatch($this->employee);
                $this->dispatchedJobs[$this->employee->id] = true;
                Flux::toast(variant: 'warning', text: 'Génération du bulletin en cours...');
            }
        } else {
            $this->validatePayslip = $this->employee->payslip->status === StatusEnum::APPROVED;
        }
    }

    public function refreshPayslip(): void
    {
        if (! $this->employee) {
            return;
        }

        ProcessPayslipDocumentJob::dispatch($this->employee, StatusEnum::PENDING);
        CalculatePayslipJob::dispatch($this->employee);
        unset($this->employee);

        Flux::toast(variant: 'success', text: 'Le bulletin sera rafraîchi');
    }

    public function refreshAllPayslip(): void
    {
        Employee::active()->notInternship()
            ->with(['payslip:id,employee_id,status'])
            ->get(['id', 'name'])
            ->each(function ($emp) {
                ProcessPayslipDocumentJob::dispatch($emp, StatusEnum::PENDING);
                CalculatePayslipJob::dispatch($emp);
            });

        Flux::toast(variant: 'success', text: 'Tous les bulletins seront rafraîchis');
    }

    public function updatedValidatePayslip(bool $value): void
    {
        if (! $this->employee?->payslip) {
            Flux::toast(variant: 'danger', text: __("Le bulletin n'a pas encore été généré"));

            return;
        }

        $status = $value ? StatusEnum::APPROVED : StatusEnum::PENDING;

        $this->employee->payslip()->update(['status' => $status]);
        ProcessPayslipDocumentJob::dispatch($this->employee, $status);

        // Invalide les listes ET l'employé affiché
        unset($this->employee, $this->pendingEmployees, $this->validatedEmployees);

        Flux::toast(
            variant: 'success',
            text: $status === StatusEnum::APPROVED
                ? __('Bulletin validé avec succès')
                : __('Bulletin invalidé avec succès'),
        );
    }

    public function validateAllPayslip(): void
    {
        $this->updateAllPayslipsStatus(StatusEnum::APPROVED);
    }

    public function invalidateAllPayslip(): void
    {
        $this->updateAllPayslipsStatus(StatusEnum::PENDING);
    }

    protected function updateAllPayslipsStatus(StatusEnum $status): void
    {
        Gate::authorize('validated', Payslip::class);

        $employees = Employee::active()->notInternship()
            ->with(['payslip:id,employee_id,status'])
            ->get(['id', 'name']);

        // ✅ 1 seul UPDATE batch au lieu de N updates
        Payslip::whereIn('employee_id', $employees->pluck('id'))->update(['status' => $status]);

        foreach ($employees as $emp) {
            $emp->payslip
                ? ProcessPayslipDocumentJob::dispatch($emp, $status)
                : CalculatePayslipJob::dispatch($emp);
        }

        unset($this->pendingEmployees, $this->validatedEmployees);

        Flux::toast(
            variant: 'success',
            text: $status === StatusEnum::APPROVED
                ? __('Tous les bulletins ont été validés')
                : __('Tous les bulletins ont été invalidés'),
        );
    }

    public function downloadPdf()
    {
        Gate::authorize('download', Payslip::class);
        if (! $this->employee) {
            return;
        }

        $pdf = Pdf::loadView('pdf.payslip', ['employee' => $this->employee]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $this->employee->name.'_'.now()->format('F_Y').'_payslip.pdf',
        );
    }

    public function downloadAllPdf()
    {
        Gate::authorize('download', Payslip::class);

        $employees = Employee::active()->notInternship()
            ->with(['payslip'])
            ->get();

        if ($employees->isEmpty()) {
            return;
        }

        $pdf = Pdf::loadView('pdf.bulk-payslip', ['employees' => $employees]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'bulletins_paie_'.now()->format('m-Y').'.pdf',
        );
    }

    public function with(): array
    {
        return [
            'salaries' => $this->employee?->payslip?->formatted_salaries ?? [],
            'contributions' => $this->employee?->payslip?->formatted_contributions ?? [],
        ];
    }
};
?>
<div>
    {{-- En-tête --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div>
            <flux:heading size="xl">{{ __('Valider les bulletins') }}</flux:heading>
            <flux:text variant="subtle">{{ __('Consultez et validez les bulletins de paie') }}</flux:text>
        </div>
        <div class="flex items-center gap-4">
            <flux:dropdown>
                <flux:button icon="bars-3" />
                <flux:menu>
                    <flux:menu.item wire:click="downloadAllPdf" icon="arrow-down-tray">Télécharger tous</flux:menu.item>
                    <flux:menu.item wire:click="refreshAllPayslip" icon="arrow-path-rounded-square">Actualiser tous</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="validateAllPayslip" icon="document-check">Valider tous</flux:menu.item>
                    <flux:menu.item wire:click="invalidateAllPayslip" icon="x-mark" variant="danger">Invalider tous</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    <div class="flex h-screen relative rounded-xl overflow-hidden">

        @if (!$readyToLoad)
        <div class="absolute inset-0 bg-white dark:bg-zinc-900 z-50 flex items-center justify-center">
            <div class="flex items-center gap-3 bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-700">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <div>
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">Chargement des données</p>
                    <p class="text-sm text-zinc-500">Calcul des bulletins en cours...</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Sidebar liste employés --}}
        <div class="w-72 flex-shrink-0 flex flex-col bg-zinc-900 border-r border-zinc-800 h-full">

            <div class="p-4 border-b border-zinc-800 space-y-3">
                <h3 class="text-base font-semibold text-white">Employés</h3>

                {{-- ✅ Champ recherche amélioré avec bouton clear --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Rechercher un employé..."
                        class="w-full pl-9 pr-8 py-2 text-sm rounded-lg bg-zinc-800 border border-zinc-700
                               text-zinc-100 placeholder-zinc-500
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                    />
                    @if(filled($search))
                    <button
                        wire:click="clearSearch"
                        class="absolute inset-y-0 right-2 flex items-center text-zinc-500 hover:text-zinc-300 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    @endif
                </div>

                @if(filled($search))
                <p class="text-xs text-zinc-500">
                    {{ $this->pendingEmployees->count() + $this->validatedEmployees->count() }} résultat(s) pour
                    <span class="text-zinc-300 font-medium">"{{ $search }}"</span>
                </p>
                @endif
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-zinc-700 flex-shrink-0">
                <button wire:click="setTab('pending')"
                    class="flex-1 py-3 text-sm font-medium text-center transition-colors relative
                           {{ $activeTab === 'pending' ? 'text-white' : 'text-zinc-500 hover:text-zinc-300' }}">
                    En attente
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full
                                 {{ $activeTab === 'pending' ? 'bg-orange-500 text-white' : 'bg-zinc-800 text-zinc-400' }}">
                        {{ $this->pendingEmployees->count() }}
                    </span>
                    @if($activeTab === 'pending')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-orange-500"></div>
                    @endif
                </button>
                <button wire:click="setTab('validated')"
                    class="flex-1 py-3 text-sm font-medium text-center transition-colors relative
                           {{ $activeTab === 'validated' ? 'text-white' : 'text-zinc-500 hover:text-zinc-300' }}">
                    Validés
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full
                                 {{ $activeTab === 'validated' ? 'bg-green-500 text-white' : 'bg-zinc-800 text-zinc-400' }}">
                        {{ $this->validatedEmployees->count() }}
                    </span>
                    @if($activeTab === 'validated')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-green-500"></div>
                    @endif
                </button>
            </div>

            {{-- ✅ Liste unifiée — wire:key stable --}}
            <div class="flex-1 overflow-y-auto p-2">
                @php
                    $listEmployees = $activeTab === 'pending' ? $this->pendingEmployees : $this->validatedEmployees;
                    $dotColor      = $activeTab === 'pending' ? 'bg-orange-500' : 'bg-green-500';
                    $borderColor   = $activeTab === 'pending' ? 'border-orange-500' : 'border-green-500';
                @endphp

                <ul wire:key="list-{{ $activeTab }}"
                    wire:loading.class="opacity-40 pointer-events-none"
                    wire:target="selectEmployee, setTab, search, clearSearch">
                    @forelse ($listEmployees as $emp)
                    {{-- ✅ FIX CRITIQUE : UUID entre guillemets simples --}}
                    <li wire:key="{{ $activeTab }}-{{ $emp->id }}"
                        wire:click="selectEmployee('{{ $emp->id }}')"
                        class="py-2.5 px-3 rounded-lg cursor-pointer flex items-center gap-3 mb-1
                               transition-colors group border-l-2
                               {{ $selectedEmployeeId === $emp->id
                                   ? 'bg-zinc-800 ' . $borderColor
                                   : 'border-transparent hover:bg-zinc-800/60' }}">

                        <div class="w-2 h-2 rounded-full {{ $dotColor }} shrink-0"></div>

                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="truncate text-sm font-medium
                                         {{ $selectedEmployeeId === $emp->id ? 'text-white' : 'text-zinc-300 group-hover:text-white' }}">
                                {{ $emp->name }}
                            </span>
                            @if($emp->job_title)
                            <span class="truncate text-xs text-zinc-500 group-hover:text-zinc-400">
                                {{ $emp->job_title }}
                            </span>
                            @endif
                        </div>

                        @if($selectedEmployeeId === $emp->id)
                        <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        @endif
                    </li>
                    @empty
                    <div class="flex flex-col items-center justify-center py-10 text-zinc-600 select-none">
                        @if(filled($search))
                            <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                            </svg>
                            <p class="text-sm font-medium text-zinc-500">Aucun résultat</p>
                            <p class="text-xs text-zinc-600 mt-1">pour "{{ $search }}"</p>
                        @elseif($activeTab === 'pending')
                            <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <p class="text-sm">Aucun bulletin en attente</p>
                        @else
                            <svg class="w-8 h-8 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                            <p class="text-sm">Aucun bulletin validé</p>
                        @endif
                    </div>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Zone droite aperçu --}}
        <div class="flex-1 p-4 overflow-y-auto relative bg-gray-50 dark:bg-zinc-950"
             wire:loading.class="overflow-hidden" wire:target="selectEmployee">

            <div wire:loading wire:target="selectEmployee"
                 class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 z-50 flex items-center justify-center backdrop-blur-sm">
                <div class="bg-white dark:bg-zinc-800 p-4 rounded-lg shadow-xl flex items-center gap-3 border border-zinc-200 dark:border-zinc-700">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span class="font-medium text-sm text-zinc-800 dark:text-zinc-200">Chargement du bulletin...</span>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    @if ($this->employee)
                        Bulletin — {{ $this->employee->name }}
                        <span class="text-sm font-normal text-gray-500 dark:text-zinc-400">
                            {{ now()->translatedFormat('F Y') }}
                        </span>
                    @else
                        Aperçu du bulletin
                    @endif
                </h3>
                @if ($selectedEmployeeId)
                <div class="flex items-center gap-3">
                    <flux:button variant="primary" wire:click="downloadPdf" icon="arrow-down-tray" size="sm" />
                    <flux:button wire:click="refreshPayslip" icon="arrow-path-rounded-square" size="sm" />
                    <div class="flex items-center gap-2 bg-white dark:bg-zinc-800 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <flux:switch wire:model.live="validatePayslip" label="Valider" />
                    </div>
                </div>
                @endif
            </div>

            @if ($selectedEmployeeId && $this->employee && $this->employee->payslip)
                @include('pdf.payslip-content', [
                    'employee'      => $this->employee,
                    'salaries'      => $salaries,
                    'contributions' => $contributions,
                ])
            @elseif($selectedEmployeeId && $this->employee && !$this->employee->payslip)
                <div wire:poll.5s="checkPayslip"
                     class="flex flex-col items-center justify-center h-[60vh] text-zinc-500">
                    <svg class="w-16 h-16 mb-4 text-zinc-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-medium text-zinc-900 dark:text-white">Bulletin en cours de génération...</p>
                    <p class="text-sm mt-1">Actualisation automatique toutes les 5 secondes.</p>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-[60vh] text-gray-400 dark:text-zinc-600 select-none">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-lg font-medium">Sélectionnez un employé</p>
                    <p class="text-sm mt-1">Choisissez un employé dans la liste pour visualiser son bulletin.</p>
                </div>
            @endif
        </div>
    </div>
</div>