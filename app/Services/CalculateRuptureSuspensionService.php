<?php

namespace App\Services;

use App\Enums\ImpactEnum;
use App\Enums\LeaveTypeEnum;
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

    /**
     * Gere le cas de la mise à pied diciplinaire
     *
     * @return void
     */
    public function disciplinary()
    {
        $days = app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate);
        $amount = ($this->employee->base_salary / 30) * $days;

        $addRemun = $this->employee->remunerations()->create([
            'name' => RemunerationEnum::RETENUE_SANCTION->value,
            'type' => RemunerationTypeEnum::RETENU->value,
            'amount' => $amount,
            'periodicity' => PeriodicityEnum::UNIQUE->value,
            'impact' => ImpactEnum::AUTRE->value,
            'notes' => "Mise à pied disciplinaire allant du $this->startDate au $this->endDate",
        ]);

        $addLeave = $this->employee->leaves()->create([
            'type' => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date' => $this->adjustToMondayIfWeekend($this->endDate),
            'days' => app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate),
            'status' => StatusEnum::APPROVED->value,
            'notes' => "Absences pour mise à pied disciplinaire allant du $this->startDate au $this->endDate",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);

    }

    /**
     * Gere le cas de la mise à pied conservatoire
     *
     * @return void
     */
    public function conservatoire()
    {
        if (empty($this->endDate)) {
            $this->endDate = Carbon::parse($this->endDate)->endOfMonth();
        }

        $days = app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate);
        $amount = ($this->employee->base_salary / 30) * $days;

        $addRemun = $this->employee->remunerations()->create([
            'name' => RemunerationEnum::RETENUE_SANCTION->value,
            'type' => RemunerationTypeEnum::RETENU->value,
            'amount' => $amount,
            'periodicity' => PeriodicityEnum::UNIQUE->value,
            'impact' => ImpactEnum::AUTRE->value,
            'notes' => "Mise à pied conservatoire allant du $this->startDate au $this->endDate",
        ]);

        $addLeave = $this->employee->leaves()->create([
            'type' => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date' => $this->adjustToMondayIfWeekend($this->endDate),
            'days' => app(CalculateDays::class)->calculateDays($this->startDate, $this->endDate),
            'status' => StatusEnum::APPROVED->value,
            'notes' => "Absences pour mise à pied conservatoire allant du $this->startDate au $this->endDate",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);

    }

    /**
     * Gere les cas des congés de maternité
     *
     * @return void
     */
    public function maternity()
    {
        $endDate = $this->adjustToMondayIfWeekend(Carbon::parse($this->startDate)->addWeeks(14));

        $addLeave = $this->employee->leaves()->create([
            'type' => LeaveTypeEnum::MATERNITY->value,
            'start_date' => $this->startDate,
            'end_date' => $endDate,
            'days' => 98,
            'status' => StatusEnum::APPROVED->value,
            'notes' => "Congé de maternité allant du $this->startDate au $endDate",
        ]);
        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function technicalUnemployment()
    {
        $techUnemployment = app(CalculateTechnicalUnemploymentService::class)->handle($this->employee, $this->month);

        $addRemun = $this->employee->remunerations()->create([
            'name' => RemunerationEnum::INDEMNITE_CHOMAGE_TECHNIQUE->value,
            'type' => RemunerationTypeEnum::INDEMNITE->value,
            'amount' => $techUnemployment,
            'periodicity' => PeriodicityEnum::MONTHLY->value,
            'impact' => ImpactEnum::TAXCOT->value,
            'notes' => "Mise en chômage technique allant du $this->startDate au $this->endDate",
        ]);

        $end = Carbon::parse($this->startDate)->addMonths($this->month);

        $addLeave = $this->employee->leaves()->create([
            'type' => LeaveTypeEnum::SUSPENSION->value,
            'start_date' => $this->startDate,
            'end_date' => $this->adjustToMondayIfWeekend($end),
            'days' => app(CalculateDays::class)->calculateDays($this->startDate, $end),
            'status' => StatusEnum::APPROVED->value,
            'notes' => "Absences pour mise à pied disciplinaire allant du $this->startDate au $end",
        ]);

        $this->setUpdateJob($this->employee, $this->startDate);
    }

    public function dismissal()
    {

        $unemployment = app(CalculateUnemployementService::class)->handle($this->employee);

        if ($this->notice_indemnity != 0 && ! $this->preavis) {

            $this->employee->remunerations()->create([
                'name' => RemunerationEnum::INDEMNITE_PREAVIS->value,
                'type' => $this->employeeRefusPreavis === false ? RemunerationTypeEnum::INDEMNITE->value : RemunerationTypeEnum::RETENU->value,
                'amount' => $this->notice_indemnity,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact' => $this->employeeRefusPreavis === false ? ImpactEnum::TAXCOT->value : ImpactEnum::NEUTRE->value,
                'notes' => $this->employeeRefusPreavis === false ? 'Indemnité de préavis' : 'Indemnité compensatrise du préavis',
            ]);
        }

        if ($this->leaves != 0) {
            $this->employee->remunerations()->create([
                'name' => RemunerationEnum::INDEMNITE_COMPENSATRISE_CONGE_PAYE->value,
                'type' => RemunerationTypeEnum::INDEMNITE->value,
                'amount' => $this->leaves,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact' => ImpactEnum::TAXCOT->value,
                'notes' => 'Indemnité compensatrise du congé payé',
            ]);
        }
        if ($unemployment != 0) {
            $addRemun = $this->employee->remunerations()->create([
                'name' => RemunerationEnum::INDEMNITE_LICENCIEMENT->value,
                'type' => RemunerationTypeEnum::INDEMNITE->value,
                'amount' => $unemployment,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact' => ImpactEnum::NEUTRE->value,
                'notes' => 'Indemnité de licenciement.',
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
                'name' => RemunerationEnum::INDEMNITE_PREAVIS->value,
                'type' => $this->employeeRefusPreavis === false ? RemunerationTypeEnum::INDEMNITE->value : RemunerationTypeEnum::RETENU->value,
                'amount' => $this->notice_indemnity,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact' => $this->employeeRefusPreavis === false ? ImpactEnum::TAXCOT->value : ImpactEnum::NEUTRE->value,
                'notes' => $this->employeeRefusPreavis === false ? 'Indemnité de préavis' : 'Indemnité compensatrise du préavis',
            ]);
        }
        if ($this->leaves != 0) {
            $this->employee->remunerations()->create([
                'name' => RemunerationEnum::INDEMNITE_COMPENSATRISE_CONGE_PAYE->value,
                'type' => RemunerationTypeEnum::INDEMNITE->value,
                'amount' => $this->leaves,
                'periodicity' => PeriodicityEnum::UNIQUE->value,
                'impact' => ImpactEnum::TAXCOT->value,
                'notes' => 'Indemnité compensatrise du congé payé',
            ]);
        }
        if (! $this->preavis) {

            $this->setUpdateJob($this->employee, $this->startDate, StatusEnum::TERMINATED->value);
        }
    }

    /**
     * set the job to  update the status of the employee
     *
     * @param  Employee  $employee
     * @param  string|Carbon  $startDate
     * @param  string|Carbon  $status
     * @return void
     */
    private function setUpdateJob($employee, $startDate, $status = StatusEnum::SUSPEND->value)
    {
        $delay = Carbon::parse($startDate) <= now() ? now() : Carbon::parse($startDate)->startOfDay();

        UpdateStatusEmployeeJob::dispatch($employee, $status)->delay($delay);
    }

    /**
     * Proroger la date a un lundi si elle tombe un weeknd
     *
     * @return string|Carbon
     */
    private function adjustToMondayIfWeekend(string|Carbon $date)
    {
        $date = Carbon::parse($date);
        if ($date->isWeekend()) {
            $date->next(Carbon::MONDAY);
        }

        return $date;
    }
}
