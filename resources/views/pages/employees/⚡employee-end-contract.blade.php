<?php

use App\Models\Employee;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Suspension rupture de contrat')] class extends Component
{
    #[Url]
    public string|int $employee;
    public string $procedureType = 'suspension';
    public string $suspensionReason = 'illness';
    public string $ruptureReason = 'dismissal';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $notificationDate = null;
    public ?string $effectiveDate = null;
    public ?int $disciplinaryDays = null;
    public ?float $monthlyAverageGross = null;
    public ?float $remainingCddMonths = null;
    public ?string $notes = null;

    public function mount(): void
    {
        $this->monthlyAverageGross = (float) ($this->employee()->data['average_salary'] ?? $this->employee()->base_salary ?? 0);
        $this->notificationDate = now()->toDateString();
        $this->effectiveDate = now()->toDateString();
    }

    public function saveProcedure(): void
    {
        $data = $this->validate([
            'procedureType' => ['required', 'in:suspension,rupture'],
            'suspensionReason' => ['required_if:procedureType,suspension'],
            'ruptureReason' => ['required_if:procedureType,rupture'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'notificationDate' => ['nullable', 'date'],
            'effectiveDate' => ['nullable', 'date'],
            'disciplinaryDays' => ['nullable', 'integer', 'min:1', 'max:8'],
            'monthlyAverageGross' => ['required', 'numeric', 'min:0'],
            'remainingCddMonths' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee = $this->employee;
        $meta = $employee->data ?? [];
        $meta['contract_procedures'] ??= [];

        $entry = array_merge($data, [
            'created_at' => now()->toDateTimeString(),
            'summary' => $this->calculateSummary($employee, $data),
        ]);

        $meta['contract_procedures'][] = $entry;
        $meta['current_contract_state'] = $data['procedureType'];

        if ($data['procedureType'] === 'rupture') {
            $employee->status = 'terminated';
            $employee->end_date = $data['effectiveDate'] ? Carbon::parse($data['effectiveDate']) : now();
        }

        $employee->data = $meta;
        $employee->save();

        Flux::toast(variant: 'success', text: __('Procédure enregistrée avec succès.'));
    }

    protected function calculateSummary(Employee $employee, array $data): array
    {
        $start = Carbon::parse($employee->start_date);
        $end = isset($data['effectiveDate']) && $data['effectiveDate'] ? Carbon::parse($data['effectiveDate']) : now();
        $years = max(0, $start->diffInMonths($end) / 12);
        $salary = (float) $data['monthlyAverageGross'];

        $summary = [
            'anciennete_annees' => round($years, 2),
        ];

        if ($data['procedureType'] === 'suspension') {
            $summary['indemnite_estimee'] = match ($data['suspensionReason']) {
                'technical_unemployment' => round($salary * 0.5, 2),
                'illness' => round($salary, 2),
                default => 0,
            };
            return $summary;
        }

        if ($employee->contract_type === 'CDD' && ($data['remainingCddMonths'] ?? null)) {
            $summary['dommages_cdd_estimes'] = round($salary * (float) $data['remainingCddMonths'], 2);
            return $summary;
        }

        $summary['indemnite_licenciement_estimee'] = round($this->calculateDismissalAllowance($salary, $years), 2);
        $summary['minimum_dommages_abusifs'] = round($salary * 3, 2);
        $summary['maximum_dommages_abusifs'] = round($salary * max(1, floor($years)), 2);

        return $summary;
    }

    protected function calculateDismissalAllowance(float $salary, float $years): float
    {
        $bands = [
            ['limit' => 5, 'rate' => 0.20],
            ['limit' => 10, 'rate' => 0.25],
            ['limit' => 15, 'rate' => 0.30],
            ['limit' => 20, 'rate' => 0.35],
            ['limit' => INF, 'rate' => 0.40],
        ];

        $remaining = $years;
        $from = 0.0;
        $total = 0.0;

        foreach ($bands as $band) {
            $capacity = $band['limit'] === INF ? $remaining : max(0, min($remaining, $band['limit'] - $from));
            $total += $capacity * $salary * $band['rate'];
            $remaining -= $capacity;
            $from = $band['limit'];
            if ($remaining <= 0) {
                break;
            }
        }

        return $total;
    }

    #[Computed()]
    public function employee()
    {
        return Employee::whereId($this->employee)
            ->firstOrFail();
    }
};
?>
<div>
    <div class="space-y-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href='{{ route("employees") }}'>{{ __('Employé') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href='{{ route("employees.profil", ["id" => $this->employee->id]) }}'>{{ $this->employee->shortName }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('Suspension / rupture') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:heading size="xl">{{ __('Gestion de la suspension et de la rupture de contrat') }}</flux:heading>

        <form wire:submit="saveProcedure" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:select wire:model.live="procedureType" :label="__('Type de procédure')">
                <option value="suspension">{{ __('Suspension') }}</option>
                <option value="rupture">{{ __('Rupture') }}</option>
            </flux:select>

            @if ($this->procedureType === 'suspension')
                <flux:select wire:model="suspensionReason" :label="__('Motif de suspension')">
                    <option value="illness">{{ __('Maladie non professionnelle') }}</option>
                    <option value="work_accident">{{ __('Accident du travail / maladie professionnelle') }}</option>
                    <option value="maternity">{{ __('Maternité') }}</option>
                    <option value="technical_unemployment">{{ __('Chômage technique') }}</option>
                    <option value="disciplinary">{{ __('Mise à pied disciplinaire') }}</option>
                </flux:select>
            @else
                <flux:select wire:model="ruptureReason" :label="__('Motif de rupture')">
                    <option value="dismissal">{{ __('Licenciement') }}</option>
                    <option value="resignation">{{ __('Démission') }}</option>
                    <option value="mutual_agreement">{{ __('Accord écrit des parties') }}</option>
                    <option value="gross_misconduct">{{ __('Faute lourde') }}</option>
                    <option value="force_majeure">{{ __('Force majeure') }}</option>
                </flux:select>
            @endif

            <flux:input type="date" wire:model="startDate" :label="__('Date de début')" />
            <flux:input type="date" wire:model="endDate" :label="__('Date de fin (si applicable)')" />
            <flux:input type="date" wire:model="notificationDate" :label="__('Date de notification écrite')" />
            <flux:input type="date" wire:model="effectiveDate" :label="__('Date d\'effet')" />
            <flux:input type="number" wire:model="disciplinaryDays" :label="__('Jours ouvrables de mise à pied')" min="1" max="8" />
            <flux:input type="number" step="0.01" wire:model="monthlyAverageGross" :label="__('Salaire moyen brut mensuel (12 derniers mois)')" />
            <flux:input type="number" step="0.01" wire:model="remainingCddMonths" :label="__('Mois restants (CDD - rupture anticipée)')" />
            <flux:textarea wire:model="notes" :label="__('Notes juridiques / RH')" rows="4" class="md:col-span-2" />

            <div class="md:col-span-2">
                <flux:button variant="primary" type="submit">{{ __('Enregistrer la procédure') }}</flux:button>
            </div>
        </form>
    </div>
</div>
