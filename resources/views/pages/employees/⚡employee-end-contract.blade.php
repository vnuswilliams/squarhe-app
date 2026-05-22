<?php

use App\Enums\MotifEnum;
use App\Models\Employee;
use App\Services\CalculateDays;
use App\Services\CalculateRuptureSuspensionService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Suspension rupture de contrat')] class extends Component
{
    #[Url]
    public string|int $employee;

    // ── Propriété classique chargée dans mount() ──
    // #[Computed] cause des checksums instables entre requêtes en Livewire 4

    public $preavis = true;

    public bool $employeeRefusPreavis = false;

    public string $motif = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?int $month = 1;

    public ?int $leaves = 0;

    public int $notice_indemnity = 0;

    public int $notice_days = 0;

    public array $previewData = [];

    #[Computed]
    public function endEmployee(): Employee
    {
        return Employee::findOrFail($this->employee);
    }

    private function service(): CalculateRuptureSuspensionService
    {
        return app(CalculateRuptureSuspensionService::class, [
            'employee' => $this->endEmployee,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'month' => $this->month,
            'leaves' => $this->leaves,
            'employeeRefusPreavis' => $this->employeeRefusPreavis,
            'notice_days' => $this->notice_days,
            'notice_indemnity' => $this->notice_indemnity,
            'preavis' => $this->preavis,
        ]);
    }

    public function updatedPreavis($value): void
    {
        if ($value) {
            $this->notice_indemnity = 0;
        } else {
            $this->notice_days = 0;
        }
    }

    public function preview(): void
    {
        $this->validate([
            'motif' => ['required', Rule::in(MotifEnum::values())],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'month' => ['nullable', 'numeric', Rule::requiredIf($this->motif === MotifEnum::TECHNICAL_UNEMPLOYMENT->value)],
        ]);

        if ($this->motif === MotifEnum::DISCIPLINARY->value) {
            if (app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate) > 8) {
                $this->addError('endDate', 'La mise à pied disciplinaire ne peut excéder 8 jours.');

                return;
            }
        }

        if ($this->motif === MotifEnum::MATERNITY->value) {
            if ($this->endEmployee->civility) {
                $this->addError('motif', 'Un homme ne peut avoir un congé de maternité.');

                return;
            }
        }

        if ($this->motif === MotifEnum::TECHNICAL_UNEMPLOYMENT->value) {
            if ($this->month < 1 || $this->month > 6) {
                $this->addError('month', "La durée d'un chômage technique est de 1 à 6 mois.");

                return;
            }
        }

        $this->previewData = $this->service()->preview($this->motif);

        $this->js("\$flux.modal('preview-confirmation').show()");
    }

    public function confirm(): void
    {
        $this->js("\$flux.modal('preview-confirmation').close()");

        $service = $this->service();

        match ($this->motif) {
            MotifEnum::DISCIPLINARY->value => $service->disciplinary(),
            MotifEnum::CONSERVATOIRE->value => $service->conservatoire(),
            MotifEnum::MATERNITY->value => $service->maternity(),
            MotifEnum::TECHNICAL_UNEMPLOYMENT->value => $service->technicalUnemployment(),
            MotifEnum::DISMISSAL->value => $service->dismissal(),
            MotifEnum::RESIGNATION->value => $service->resignation(),
        };

        Flux::toast(variant: 'success', text: __('Procédure enregistrée avec succès.'));
    }
};
?>

<div>
    <div class="space-y-6">

        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('employees') }}">{{ __('Employé') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href='{{ route("employees.show", ["id" => $this->endEmployee->id]) }}'>
                {{ $this->endEmployee->shortName }}
            </flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('Suspension / rupture') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:heading size="xl">{{ __('Gestion de la suspension et de la rupture de contrat') }}</flux:heading>

        <form wire:submit="preview" class="grid grid-cols-1 gap-4 md:grid-cols-2">

            <flux:select wire:model.live="motif" :label="__('Motif de suspension ou de rupture')">
                <flux:select.option value="">Choisir une option</flux:select.option>
                @foreach (App\Enums\MotifEnum::options() as $option)
                    <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>

            @if (!empty($motif))
                <flux:callout>
                    <flux:callout.heading>Information</flux:callout.heading>
                    <flux:callout.text>{{ App\Enums\MotifEnum::from($motif)->description() }}</flux:callout.text>
                </flux:callout>
            @endif

            @if (in_array($motif, [MotifEnum::RESIGNATION->value, MotifEnum::DISMISSAL->value]))
                <div class="md:col-span-2 space-y-4 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:switch wire:model.live="preavis" label="Préavis sera effectué ?" />

                    @if ($preavis)
                        <flux:input wire:model="notice_days" type="number" min="1"
                            label="Combien de jours de préavis ?" placeholder="Ex: 30" />
                    @else
                        <flux:input wire:model="notice_indemnity" type="number" min="0"
                            label="Indemnité de préavis (FCFA)" placeholder="Ex: 150 000" />
                        <flux:switch wire:model="employeeRefusPreavis"
                            label="L'employé ne souhaite pas effectuer le préavis ?" />
                    @endif

                    <flux:input type="number" wire:model="leaves"
                        :label="__('Indemnité compensatrice du congé payé (FCFA)')" placeholder="Ex: 80 000" />
                </div>
            @endif

            <flux:input type="date" wire:model="startDate" :label="__('Date de début')" />
            <flux:input type="date" wire:model="endDate" :label="__('Date de fin (si applicable)')" />

            @if ($motif === MotifEnum::TECHNICAL_UNEMPLOYMENT->value)
                <flux:input type="number" wire:model="month" :label="__('Durée (en mois, 1 à 6)')" min="1" max="6" />
            @endif

            <div class="md:col-span-2">
                <flux:button variant="primary" type="submit">
                    {{ __('Prévisualiser la procédure') }}
                </flux:button>
            </div>

        </form>
    </div>

    <flux:modal name="preview-confirmation" class="w-full max-w-md">
        <div class="space-y-5">

            <div>
                <flux:heading size="lg">Confirmer la procédure</flux:heading>
                <flux:subheading>{{ $this->endEmployee->shortName }}</flux:subheading>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                @forelse ($previewData as $item)
                    <div class="flex items-baseline justify-between gap-4 px-4 py-3 text-sm">
                        <div>
                            <span class="text-zinc-800 dark:text-zinc-200">{{ $item['label'] }}</span>
                            @if (!empty($item['detail']))
                                <p class="text-xs text-zinc-400 mt-0.5">{{ $item['detail'] }}</p>
                            @endif
                        </div>
                        @if ($item['amount'] !== null)
                            <span class="shrink-0 font-semibold tabular-nums {{ $item['amount'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $item['amount'] < 0 ? '-' : '+' }} {{ number_format(abs($item['amount']), 0, ',', ' ') }} F
                            </span>
                        @else
                            <span class="shrink-0 text-xs text-zinc-400">—</span>
                        @endif
                    </div>
                @empty
                    <p class="px-4 py-3 text-sm text-zinc-400">Aucun élément calculé.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" @click="$flux.modal('preview-confirmation').close()">
                    Annuler
                </flux:button>
                <flux:button variant="primary">
                    Confirmer
                </flux:button>
            </div>

        </div>
    </flux:modal>

</div>