<?php

use App\Enums\LawEnum;
use App\Enums\PaymentEnum;
use App\Models\Company;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
new #[Title('Modifier les paramètres de la société')] class extends Component {
    // Form properties
    public $rav = true;
    public $tdl = true;
    public $irpp = true;
    public $labourHours = 173.33;
    public $paymentMethod = '';
    public $applicable_law = '';

    public $leaves = [];
    public $seniorityBonus = [];
    public $familyAllowances = [];
    public $accident = [];
    public $oldAgePension = [];
    public $cfc = [];
    public $cac = [];
    public $fne = [];
    public $fixedHolidays = [];

    // Options from config
    public $labourHoursOptions = [];

    // Store original full data to preserve rates/shares not in form
    protected $originalSettings = [];

    public function mount(): void
    {
        if (!$this->company) {
            Flux::toast(variant: 'success', text: __('toast.createCompany'));
            $this->redirect(route('settings.company.add'), navigate: true);
        }
        // Deafults based on provided JSON structure
        $defaults = config('squarhe.defaults');

        $saved = $this->company?->data ?? [];

        $settings = array_replace_recursive($defaults, $saved);
        $this->originalSettings = $settings;

        // Map to properties
        $this->rav = $settings['rav'] ?? true;
        $this->tdl = $settings['tdl'] ?? true;
        $this->irpp = $settings['irpp'] ?? true;
        $this->labourHours = $settings['labourHours'] ?? 173.33;
        $this->paymentMethod = $settings['paymentMethod'] ?? '';
        $this->applicable_law = $settings['applicable_law'] ?? '';

        $this->leaves = $settings['leaves'] ?? [];
        $this->seniorityBonus = $settings['seniorityBonus'] ?? [];
        $this->familyAllowances = $settings['familyAllowances'] ?? [];
        $this->accident = $settings['accident'] ?? [];
        $this->oldAgePension = $settings['oldAgePension'] ?? [];
        $this->cfc = $settings['cfc'] ?? [];
        $this->cac = $settings['cac'] ?? [];
        $this->fne = $settings['fne'] ?? [];

        $holidays = $settings['fixedHolidays'] ?? config('squarhe.fixedHolidays', []);
        $currentYear = date('Y');
        $this->fixedHolidays = array_map(function ($date) use ($currentYear) {
            return strlen($date) === 5 ? $currentYear . '-' . $date : $date;
        }, $holidays);

        // Load options
        $this->labourHoursOptions = config('squarhe.settingsCompany.labourHours', []);
    }
    #[Computed]
    public function company()
    {
        return auth()->user()?->company;
    }
    public function save()
    {
        $this->authorize('update', $this->company);

        $validated = $this->validate([
            'rav' => 'boolean',
            'tdl' => 'boolean',
            'irpp' => 'boolean',
            'labourHours' => 'numeric',
            'paymentMethod' => 'nullable|string',
            'leaves.monthlyLeave' => 'numeric',
            'leaves.seniorLeave' => 'numeric',
            'leaves.childLeave' => 'numeric',
            'seniorityBonus.enabled' => 'boolean',
            'familyAllowances.enabled' => 'boolean',
            'accident.enabled' => 'boolean',
            'oldAgePension.enabled' => 'boolean',
            'cfc.enabled' => 'boolean',
            'cac.enabled' => 'boolean',
            'fne.enabled' => 'boolean',
            'applicable_law' => 'nullable|string',
            'fixedHolidays' => 'array',
            'fixedHolidays.*' => 'required|date',
        ]);

        // Merge form updates into original settings to preserve rates
        $newData = $this->company?->data ?? [];

        // Simple scalar values
        $newData['rav'] = $this->rav;
        $newData['tdl'] = $this->tdl;
        $newData['irpp'] = $this->irpp;
        $newData['labourHours'] = $this->labourHours;
        $newData['paymentMethod'] = $this->paymentMethod;
        $newData['applicable_law'] = $this->applicable_law;
        $newData['leaves'] = $this->leaves;

        $newData['fixedHolidays'] = array_map(function ($date) {
            return date('m-d', strtotime($date));
        }, $this->fixedHolidays);

        // Nested objects: update 'enabled', preserve others
        foreach (['seniorityBonus', 'familyAllowances', 'accident', 'oldAgePension', 'cfc', 'cac', 'fne'] as $key) {
            if (!isset($newData[$key])) {
                $newData[$key] = [];
            }
            $newData[$key]['enabled'] = $this->$key['enabled'] ?? false;

            // Re-merge protected fields from original loaded data if they were lost
            if (isset($this->originalSettings[$key])) {
                foreach ($this->originalSettings[$key] as $subKey => $val) {
                    if ($subKey !== 'enabled') {
                        $newData[$key][$subKey] = $val;
                    }
                }
            }
        }

        $this->company?->data = $newData;
        $this->company?->save();
        Flux::toast(variant: 'success', text: __('toast.settingupdatecompanysuccess'));
    }

    public function addHoliday()
    {
        $this->fixedHolidays[] = date('Y-m-d');
    }

    public function updatedFixedHolidays()
    {
        $this->saveHolidays();
    }

    public function removeHoliday($index)
    {
        unset($this->fixedHolidays[$index]);
        $this->fixedHolidays = array_values($this->fixedHolidays);
        $this->saveHolidays();
    }

    private function saveHolidays()
    {
        $this->validate([
            'fixedHolidays' => 'array',
            'fixedHolidays.*' => 'required|date',
        ]);

        $settings = $this->company?->data ?? [];
        $settings['fixedHolidays'] = array_map(fn($date) => date('m-d', strtotime($date)), $this->fixedHolidays);

        $this->company?->data = $settings;
        $this->company?->save();
        Flux::toast(variant: 'success', text: 'toast.holidaysave');
    }
};

?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('settings.settings.company.title')" :subheading="__('Changer les paramètres de votre compagnie')">
            <form wire:submit.prevent="save" class="space-y-10">

    {{-- ═══════════════════════════════════════════════════════
         SECTION 1 — Configuration Fiscale & Sociale
    ═══════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('setting.fiscal.title') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('setting.fiscal.description') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:switch
                    :label="__('setting.fiscal.rav.label')"
                    :description="__('setting.fiscal.rav.description')"
                    wire:model.live="rav"
                />
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:switch
                    :label="__('setting.fiscal.tdl.label')"
                    :description="__('setting.fiscal.tdl.description')"
                    wire:model.live="tdl"
                />
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:switch
                    :label="__('setting.fiscal.irpp.label')"
                    :description="__('setting.fiscal.irpp.description')"
                    wire:model.live="irpp"
                />
            </div>
        </div>
    </section>

    <flux:separator />

    {{-- ═══════════════════════════════════════════════════════
         SECTION 2 — Jours Fériés
    ═══════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('setting.holidays.title') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('setting.holidays.description') }}</p>
        </div>

        <div class="space-y-3">
            @forelse ($fixedHolidays as $index => $holiday)
                <div class="flex items-center gap-3">
                    <flux:input
                        type="date"
                        wire:model.blur="fixedHolidays.{{ $index }}"
                        class="max-w-xs"
                    />
                    <flux:button
                        variant="danger"
                        icon="trash"
                        wire:click="removeHoliday({{ $index }})"
                        :aria-label="__('setting.holidays.remove')"
                    />
                </div>
            @empty
                <p class="text-sm italic text-zinc-400 dark:text-zinc-500">{{ __('setting.holidays.empty') }}</p>
            @endforelse
        </div>

        <div class="mt-4">
            <flux:button size="sm" icon="plus" wire:click="addHoliday">
                {{ __('setting.holidays.add') }}
            </flux:button>
        </div>
    </section>

    <flux:separator />

    {{-- ═══════════════════════════════════════════════════════
         SECTION 3 — Congés & Heures de travail
    ═══════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('setting.leave.title') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('setting.leave.description') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <flux:input
                :label="__('setting.leave.monthly')"
                :description="__('setting.leave.monthly_hint')"
                wire:model="leaves.monthlyLeave"
                type="number"
                min="0"
            />
            <flux:input
                :label="__('setting.leave.seniority')"
                :description="__('setting.leave.seniority_hint')"
                wire:model="leaves.seniorLeave"
                type="number"
                min="0"
            />
            <flux:input
                :label="__('setting.leave.child')"
                :description="__('setting.leave.child_hint')"
                wire:model="leaves.childLeave"
                type="number"
                min="0"
            />
            <flux:select
                :label="__('setting.labour.hours_label')"
                :description="__('setting.labour.hours_description')"
                wire:model="labourHours"
            >
                <flux:select.option value="">{{ __('setting.common.choose') }}</flux:select.option>
                @foreach ($labourHoursOptions as $key => $value)
                    <flux:select.option value="{{ $value }}">
                        {{ ucfirst($key) }} ({{ $value }})
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </section>

    <flux:separator />

    {{-- ═══════════════════════════════════════════════════════
         SECTION 4 — Cotisations & Primes
    ═══════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('setting.contributions.title') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('setting.contributions.description') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            {{-- Prime d'ancienneté --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.seniority_bonus.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.seniority_bonus.label')"
                    :description="__('setting.contributions.seniority_bonus.description')"
                    wire:model.live="seniorityBonus.enabled"
                />
            </div>

            {{-- Pension vieillesse --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.old_age_pension.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.old_age_pension.label')"
                    :description="__('setting.contributions.old_age_pension.description')"
                    wire:model.live="oldAgePension.enabled"
                />
            </div>

            {{-- Allocations familiales --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.family_allowances.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.family_allowances.label')"
                    :description="__('setting.contributions.family_allowances.description')"
                    wire:model.live="familyAllowances.enabled"
                />
            </div>

            {{-- Accident de travail --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.accident.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.accident.label')"
                    :description="__('setting.contributions.accident.description')"
                    wire:model.live="accident.enabled"
                />
            </div>

            {{-- CFC --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.cfc.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.cfc.label')"
                    :description="__('setting.contributions.cfc.description')"
                    wire:model.live="cfc.enabled"
                />
            </div>

            {{-- CAC --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.cac.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.cac.label')"
                    :description="__('setting.contributions.cac.description')"
                    wire:model.live="cac.enabled"
                />
            </div>

            {{-- FNE --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">
                    {{ __('setting.contributions.fne.category') }}
                </p>
                <flux:switch
                    :label="__('setting.contributions.fne.label')"
                    :description="__('setting.contributions.fne.description')"
                    wire:model.live="fne.enabled"
                />
            </div>

        </div>
    </section>

    <flux:separator />

    {{-- ═══════════════════════════════════════════════════════
         SECTION 5 — Paiement & Droit applicable
    ═══════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-6">
            <flux:heading size="lg">{{ __('setting.payment.title') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('setting.payment.description') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <flux:select
                :label="__('setting.payment.method.label')"
                :description="__('setting.payment.method.description')"
                wire:model="paymentMethod"
            >
                <flux:select.option value="">{{ __('setting.common.choose') }}</flux:select.option>
                @foreach (PaymentEnum::options() as $item)
                    <flux:select.option value="{{ $item['label'] }}">
                        {{ $item['label'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                :label="__('setting.payment.law.label')"
                :description="__('setting.payment.law.description')"
                wire:model="applicable_law"
            >
                <flux:select.option value="">{{ __('setting.common.choose') }}</flux:select.option>
                @foreach (LawEnum::options() as $item)
                    <flux:select.option value="{{ $item['label'] }}">
                        {{ $item['label'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════
         Actions
    ═══════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
        <flux:button variant="primary" type="submit">
            {{ __('setting.actions.save') }}
        </flux:button>
        <flux:button variant="ghost" type="button" wire:click="$refresh">
            {{ __('setting.actions.cancel') }}
        </flux:button>
    </div>

</form>
    </x-settings.layout>
</section>
