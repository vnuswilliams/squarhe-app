<?php

use App\Enums\MotifEnum;
use App\Models\Employee;
use App\Services\CalculateDays;
use App\Services\CalculateRuptureSuspensionService;
use Carbon\Carbon;
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

    public $preavis = true;

    public bool $employeeRefusPreavis = false;

    public string $motif = '';

    public string $ruptureReason = 'dismissal';

    public ?string $startDate = '';

    public ?string $endDate = '';

    public ?string $notes = '';

    public ?int $month = 1;

    public ?int $leaves = 0;

    #[Computed]
    public function endEmployee()
    {
        return Employee::whereId($this->employee)->first();
    }

    private function calculate()
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
            'preavis' => true,
        ]);
    }

    public ?int $notice_indemnity = 0;

    public ?int $notice_days = 0;

    public function updatedPreavis($value): void
    {
        if ($value) {
            $this->notice_indemnity = null;
        } else {
            $this->notice_days = null;
        }
    }

    public function save()
    {
        $this->validate([
            'motif' => ['required', Rule::in(MotifEnum::values())],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'month' => ['nullable', 'numeric', Rule::requiredIf(in_array($this->motif, [MotifEnum::TECHNICAL_UNEMPLOYMENT->value]))],
        ]);
        if ($this->motif === MotifEnum::DISCIPLINARY->value) {
            if (app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate) > 8) {
                return $this->addError(message: 'La mise à pied disciplinaire ne peut excéder 8 jours.', name: 'endDate');
            }

            return $this->calculate()->disciplinary();
        }
        if ($this->motif === MotifEnum::CONSERVATOIRE->value) {
            return $this->calculate()->conservatoire();
        }
        if ($this->motif === MotifEnum::MATERNITY->value) {
            if ($this->endEmployee->civility) {
                return $this->addError(message: 'Un homme ne peut avoir un congé de maternité.', name: 'motif');
            }
            if (
                Carbon::parse($this->startDate)
                    ->diffInWeeks(Carbon::parse($this->endDate)) > 14
            ) {
                return $this->addError(message: 'Le congé de maternité dure au minimum 14 semaines.', name: 'startDate');

            }

            return $this->calculate()->maternity();

        }
        if ($this->motif === MotifEnum::TECHNICAL_UNEMPLOYMENT->value) {
            if ($this->month < 1 || $this->month > 6) {
                return $this->addError(name: 'month', message: "La durée d'un chômage technique est de 1 à 6 mois.");
            }

            $this->calculate()->technicalUnemployment();
        }
        if ($this->motif === MotifEnum::DISMISSAL->value) {
            return $this->calculate()->dismissal();
        }

        Flux::toast(variant: 'success', text: __('Procédure enregistrée avec succès.'));
    }
};
?>
<div>
    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('employees') }}">{{ __('Employé') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href='{{ route("employees.show", ["id" => "$this->employee->id"]) }}'  >
                {{ $this->endEmployee->shortName }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('Suspension / rupture') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:heading size="xl">{{ __('Gestion de la suspension et de la rupture de contrat') }}</flux:heading>

        <form wire:submit="save" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:select wire:model.live="motif" :label="__('Motif de suspension ou de rupture')">
                <flux:select.option value="">Choisir une option</flux:select.option>
                @foreach (App\Enums\MotifEnum::options() as $option)
                    <flux:select.option value="{{ $option['value'] }}">{{ $option['label'] }}</flux:select.option>
                @endforeach
            </flux:select>
            @if (!empty($motif))
                <flux:callout>
                    <flux:callout.heading> Information </flux:callout.heading>
                    <flux:callout.text>{{ App\Enums\MotifEnum::from($motif)->description() }}</flux:callout.text>
                </flux:callout>
            @endif
        @if (in_array($motif, [MotifEnum::RESIGNATION->value, MotifEnum::DISMISSAL->value])) 
        <div class="space-y-4">

    <flux:switch
        wire:model.live="preavis"
        label="Préavis sera effectué ?"
    />

    @if($preavis)

        <flux:input            wire:model.live="notice_days"             type="number"           min="1"            label="Combien de jours de cpréavis ?"            placeholder="Ex: 30"        />

    @else

        <flux:input            wire:model.live="notice_indemnity"           type="number"            min="0"            label="Indemnité de préavis"            placeholder="Ex: 150000"        />
        <flux:switch
        wire:model.live="employeeRefusPreavis"
        label="Employé ne souhaite pas               effectué le préavis ?"
    />
    @endif

</div>
            <flux:input type="number" wire:model="leaves" :label="__('Indemnité compensatrice du congés payé ?')" />
            @endif
            @if ($motif === MotifEnum::DISMISSAL->value) 

            @endif

            <flux:input type="date" wire:model="startDate" :label="__('Date de début')" />
            <flux:input type="date" wire:model="endDate" :label="__('Date de fin (si applicable)')" />
        @if ($this->motif === MotifEnum::TECHNICAL_UNEMPLOYMENT->value) 
            <flux:input type="number" wire:model="month" :label="__('Combien de temps cela durera ?')" />
@endif
            <div class="md:col-span-2">
                <flux:button variant="primary" type="submit">{{ __('Enregistrer la procédure') }}</flux:button>
            </div>
        </form>
    </div>
</div>
