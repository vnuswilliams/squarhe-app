<?php

namespace App\Services;

use App\Enums\ImpactEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\MotifEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\StatusEnum;
use App\Jobs\UpdateStatusEmployeeJob;
use App\Models\Employee;
use Carbon\Carbon;

class CalculateRuptureSuspensionService
{
    public function __construct(
        public Employee $employee,
        public Carbon|string $startDate,
        public Carbon|string|null $endDate,
        public int $month = 1,
        public int $leaves = 0,
        public bool $preavis = true,
        public $employeeRefusPreavis = false,
        public $notice_days = 0,
        public $notice_indemnity = 0,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // PREVIEW — retourne les lignes calculées SANS toucher à la base de données
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne un tableau de lignes décrivant ce qui sera enregistré.
     * Chaque ligne : ['label' => string, 'detail' => string|null, 'amount' => int|null]
     * amount > 0 = indemnité, amount < 0 = retenue, null = pas de montant (statut, absence…)
     */
    public function preview(string $motif): array
    {
        return match ($motif) {
            MotifEnum::DISCIPLINARY->value        => $this->previewDisciplinary(),
            MotifEnum::CONSERVATOIRE->value       => $this->previewConservatoire(),
            MotifEnum::MATERNITY->value           => $this->previewMaternity(),
            MotifEnum::TECHNICAL_UNEMPLOYMENT->value => $this->previewTechnicalUnemployment(),
            MotifEnum::DISMISSAL->value           => $this->previewDismissal(),
            MotifEnum::RESIGNATION->value         => $this->previewResignation(),
            default                               => [],
        };
    }

    private function previewDisciplinary(): array
    {
        $this->endDate = $this->endDate ?? Carbon::parse($this->startDate)->addDays(8);
        $days   = Carbon::parse($this->startDate)->diffInDays($this->endDate);
        $amount = ($this->employee->base_salary / 30) * $days;

        return [
            ['label' => 'Retenue sur salaire',  'detail' => "{$days} jours ouvrés",                          'amount' => -$amount],
            ['label' => 'Absence enregistrée',  'detail' => "Du {$this->startDate} au {$this->endDate}",     'amount' => null],
            ['label' => 'Statut → Suspendu',    'detail' => "À partir du {$this->startDate}",                'amount' => null],
        ];
    }

    private function previewConservatoire(): array
    {
        $resolvedEnd = $this->endDate ?: Carbon::parse($this->startDate)->endOfMonth()->format('Y-m-d');
        $days   = Carbon::parse($this->startDate)->diffInDays($resolvedEnd);
        $amount      = ($this->employee->base_salary / 30) * $days;

        return [
            ['label' => 'Retenue sur salaire', 'detail' => "{$days} jours ouvrés",                        'amount' => -$amount],
            ['label' => 'Absence enregistrée', 'detail' => "Du {$this->startDate} au {$resolvedEnd}",     'amount' => null],
            ['label' => 'Statut → Suspendu',   'detail' => "À partir du {$this->startDate}",              'amount' => null],
        ];
    }

    private function previewMaternity(): array
    {
        $endDate = Carbon::parse($this->startDate)->addWeeks(14)->format('d/m/Y');

        return [
            ['label' => 'Congé de maternité', 'detail' => "Du {$this->startDate} au {$endDate} · 98 jours", 'amount' => null],
            ['label' => 'Statut → Suspendu',  'detail' => "À partir du {$this->startDate}",                  'amount' => null],
        ];
    }

    private function previewTechnicalUnemployment(): array
    {
        $techAmount = app(CalculateTechnicalUnemploymentService::class)->handle($this->employee, $this->month);
        $endDate    = Carbon::parse($this->startDate)->addMonths($this->month)->format('Y-m-d');
        $days       = app(CalculateDays::class)->calculateDays($this->startDate, $endDate);

        return [
            ['label' => 'Indemnité chômage technique', 'detail' => "{$this->month} mois",                                   'amount' => $techAmount],
            ['label' => 'Absence enregistrée',         'detail' => "Du {$this->startDate} au {$endDate} · {$days} jours",  'amount' => null],
            ['label' => 'Statut → Suspendu',           'detail' => "À partir du {$this->startDate}",                       'amount' => null],
        ];
    }

    private function previewDismissal(): array
    {
        $items        = [];
        $unemployment = app(CalculateUnemployementService::class)->handle($this->employee);

        if ($this->notice_indemnity != 0 && ! $this->preavis) {
            $isPenalty = $this->employeeRefusPreavis;
            $items[]   = [
                'label'  => $isPenalty ? 'Indemnité compensatrice du préavis (retenue)' : 'Indemnité de préavis',
                'detail' => null,
                'amount' => $isPenalty ? -$this->notice_indemnity : $this->notice_indemnity,
            ];
        }

        if ($this->leaves != 0) {
            $items[] = ['label' => 'Indemnité compensatrice du congé payé', 'detail' => null, 'amount' => $this->leaves];
        }

        if ($unemployment != 0) {
            $items[] = ['label' => 'Indemnité de licenciement', 'detail' => null, 'amount' => $unemployment];
        }

        if (! $this->preavis) {
            $items[] = ['label' => 'Statut → Licencié', 'detail' => "À partir du {$this->startDate}", 'amount' => null];
        }

        return $items;
    }

    private function previewResignation(): array
    {
        $items = [];

        if ($this->notice_indemnity != 0 && ! $this->preavis) {
            $isPenalty = $this->employeeRefusPreavis;
            $items[]   = [
                'label'  => $isPenalty ? 'Indemnité compensatrice du préavis (retenue)' : 'Indemnité de préavis',
                'detail' => null,
                'amount' => $isPenalty ? -$this->notice_indemnity : $this->notice_indemnity,
            ];
        }

        if ($this->leaves != 0) {
            $items[] = ['label' => 'Indemnité compensatrice du congé payé', 'detail' => null, 'amount' => $this->leaves];
        }

        if (! $this->preavis) {
            $items[] = ['label' => 'Statut → Démissionnaire', 'detail' => "À partir du {$this->startDate}", 'amount' => null];
        }

        return $items;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PERSISTANCE — méthodes d'enregistrement (inchangées)
    // ─────────────────────────────────────────────────────────────────────────

    public function disciplinary()
    {
        $this->endDate = $this->endDate ?? Carbon::parse($this->startDate)->addDays(8);
        $days   = app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate);
        $amount = ($this->employee->base_salary / 30) * $days;

        $this->employee->remunerations()->create([
            'name'        => RemunerationEnum::RETENUE_SANCTION->value,
            'type'        => RemunerationTypeEnum::RETENU->value,
            'amount'      => $amount,
            'periodicity' => PeriodicityEnum::UNIQUE->value,
            'impact'      => ImpactEnum::AUTRE->value,
            'notes'       => "Mise à pied disciplinaire allant du $this->startDate au $this->endDate",
        ]);

        $this->employee->leaves()->create([
            'type'       => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date'   => $this->adjustToMondayIfWeekend($this->endDate),
            'days'       => app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate),
            'status'     => StatusEnum::APPROVED->value,
            'notes'      => "Absences pour mise à pied disciplinaire allant du $this->startDate au $this->endDate",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function conservatoire()
    {
        if (empty($this->endDate)) {
            $this->endDate = Carbon::parse($this->startDate)->endOfMonth();
        }

        $days   = app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate);
        $amount = ($this->employee->base_salary / 30) * $days;

        $this->employee->remunerations()->create([
            'name'        => RemunerationEnum::RETENUE_SANCTION->value,
            'type'        => RemunerationTypeEnum::RETENU->value,
            'amount'      => $amount,
            'periodicity' => PeriodicityEnum::UNIQUE->value,
            'impact'      => ImpactEnum::AUTRE->value,
            'notes'       => "Mise à pied conservatoire allant du $this->startDate au $this->endDate",
        ]);

        $this->employee->leaves()->create([
            'type'       => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date'   => $this->adjustToMondayIfWeekend($this->endDate),
            'days'       => app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate),
            'status'     => StatusEnum::APPROVED->value,
            'notes'      => "Absences pour mise à pied conservatoire allant du $this->startDate au $this->endDate",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function maternity()
    {
        $endDate = $this->adjustToMondayIfWeekend(Carbon::parse($this->startDate)->addWeeks(14));

        $this->employee->leaves()->create([
            'type'       => LeaveTypeEnum::MATERNITY->value,
            'start_date' => $this->startDate,
            'end_date'   => $endDate,
            'days'       => 98,
            'status'     => StatusEnum::APPROVED->value,
            'notes'      => "Congé de maternité allant du $this->startDate au $endDate",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function technicalUnemployment()
    {
        $techUnemployment = app(CalculateTechnicalUnemploymentService::class)->handle($this->employee, $this->month);

        $this->employee->remunerations()->create([
            'name'        => RemunerationEnum::INDEMNITE_CHOMAGE_TECHNIQUE->value,
            'type'        => RemunerationTypeEnum::INDEMNITE->value,
            'amount'      => $techUnemployment,
            'periodicity' => PeriodicityEnum::MONTHLY->value,
            'impact'      => ImpactEnum::TAXCOT->value,
            'notes'       => "Mise en chômage technique allant du $this->startDate au $this->endDate",
        ]);

        $end = Carbon::parse($this->startDate)->addMonths($this->month);

        $this->employee->leaves()->create([
            'type'       => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date'   => $this->adjustToMondayIfWeekend($end),
            'days'       => app(CalculateDays::class)->calculateDays($this->startDate, $end),
            'status'     => StatusEnum::APPROVED->value,
            'notes'      => "Absences pour mise en chômage technique allant du $this->startDate au $end",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function dismissal()
    {
        $unemployment = app(CalculateUnemployementService::class)->handle($this->employee);

        if ($this->notice_indemnity != 0 && ! $this->preavis) {
            $this->employee->remunerations()->create([
                'name'        => RemunerationEnum::INDEMNITE_PREAVIS->value,
                'type'        => $this->employeeRefusPreavis === false ? RemunerationTypeEnum::INDEMNITE->value : RemunerationTypeEnum::RETENU->value,
                'amount'      => $this->notice_indemnity,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact'      => $this->employeeRefusPreavis === false ? ImpactEnum::TAXCOT->value : ImpactEnum::NEUTRE->value,
                'notes'       => $this->employeeRefusPreavis === false ? 'Indemnité de préavis' : 'Indemnité compensatrise du préavis',
            ]);
        }

        if ($this->leaves != 0) {
            $this->employee->remunerations()->create([
                'name'        => RemunerationEnum::INDEMNITE_COMPENSATRISE_CONGE_PAYE->value,
                'type'        => RemunerationTypeEnum::INDEMNITE->value,
                'amount'      => $this->leaves,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact'      => ImpactEnum::TAXCOT->value,
                'notes'       => 'Indemnité compensatrise du congé payé',
            ]);
        }

        if ($unemployment != 0) {
            $this->employee->remunerations()->create([
                'name'        => RemunerationEnum::INDEMNITE_LICENCIEMENT->value,
                'type'        => RemunerationTypeEnum::INDEMNITE->value,
                'amount'      => $unemployment,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact'      => ImpactEnum::NEUTRE->value,
                'notes'       => 'Indemnité de licenciement.',
            ]);
        }

        if (! $this->preavis) {
            $this->setUpdateJob($this->employee, $this->startDate, StatusEnum::TERMINATED->value);
        }
    }

    public function resignation()
    {
        if ($this->notice_indemnity != 0 && ! $this->preavis) {
            $this->employee->remunerations()->create([
                'name'        => RemunerationEnum::INDEMNITE_PREAVIS->value,
                'type'        => $this->employeeRefusPreavis === false ? RemunerationTypeEnum::INDEMNITE->value : RemunerationTypeEnum::RETENU->value,
                'amount'      => $this->notice_indemnity,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact'      => $this->employeeRefusPreavis === false ? ImpactEnum::TAXCOT->value : ImpactEnum::NEUTRE->value,
                'notes'       => $this->employeeRefusPreavis === false ? 'Indemnité de préavis' : 'Indemnité compensatrise du préavis',
            ]);
        }

        if ($this->leaves != 0) {
            $this->employee->remunerations()->create([
                'name'        => RemunerationEnum::INDEMNITE_COMPENSATRISE_CONGE_PAYE->value,
                'type'        => RemunerationTypeEnum::INDEMNITE->value,
                'amount'      => $this->leaves,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact'      => ImpactEnum::TAXCOT->value,
                'notes'       => 'Indemnité compensatrise du congé payé',
            ]);
        }

        if (! $this->preavis) {
            $this->setUpdateJob($this->employee, $this->startDate, StatusEnum::TERMINATED->value);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privés
    // ─────────────────────────────────────────────────────────────────────────

    private function setUpdateJob($employee, $startDate, $status = StatusEnum::SUSPEND->value)
    {
        $delay = Carbon::parse($startDate) <= now() ? now() : Carbon::parse($startDate)->startOfDay();

        UpdateStatusEmployeeJob::dispatch($employee, $status)->delay($delay);
    }

    private function adjustToMondayIfWeekend(string|Carbon $date)
    {
        $date = Carbon::parse($date);
        if ($date->isWeekend()) {
            $date->next(Carbon::MONDAY);
        }

        return $date;
    }
}