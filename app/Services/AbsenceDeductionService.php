<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AbsenceDeductionService
{
    /**
     * Calcul par la méthode du 30e.
     *
     * Exemple :
     * - Salaire : 200000
     * - 7 jours d'absence calendaires
     */
    public function byThirtyDays(
        float $monthlySalary,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $absenceDays = $startDate->diffInDays($endDate) + 1;

        $dailyRate = $monthlySalary / 30;

        $deduction = $dailyRate * $absenceDays;

        return [
            'method' => '30e',
            'daily_rate' => round($dailyRate, 2),
            'absence_days' => $absenceDays,
            'deduction' => round($deduction, 2),
        ];
    }

    /**
     * Calcul par jours ouvrables réels.
     *
     * Généralement :
     * lundi -> samedi
     */
    public function byWorkingDays(
        float $monthlySalary,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $workingDaysInMonth = $this->countWorkingDaysInMonth(
            $startDate,
            includeSaturday: true
        );

        $absenceDays = $this->countWorkingDaysBetween(
            $startDate,
            $endDate,
            includeSaturday: true
        );

        $dailyRate = $monthlySalary / $workingDaysInMonth;

        $deduction = $dailyRate * $absenceDays;

        return [
            'method' => 'jours_ouvrables',
            'days_in_month' => $workingDaysInMonth,
            'daily_rate' => round($dailyRate, 2),
            'absence_days' => $absenceDays,
            'deduction' => round($deduction, 2),
        ];
    }

    /**
     * Calcul par jours ouvrés réels.
     *
     * Généralement :
     * lundi -> vendredi
     */
    public function byWorkedDays(
        float $monthlySalary,
        Carbon $startDate,
        Carbon $endDate
    ): array {
        $workedDaysInMonth = $this->countWorkingDaysInMonth(
            $startDate,
            includeSaturday: false
        );

        $absenceDays = $this->countWorkingDaysBetween(
            $startDate,
            $endDate,
            includeSaturday: false
        );

        $dailyRate = $monthlySalary / $workedDaysInMonth;

        $deduction = $dailyRate * $absenceDays;

        return [
            'method' => 'jours_ouvres',
            'days_in_month' => $workedDaysInMonth,
            'daily_rate' => round($dailyRate, 2),
            'absence_days' => $absenceDays,
            'deduction' => round($deduction, 2),
        ];
    }

    /**
     * Calcul par heures réelles.
     */
    public function byHours(
        float $monthlySalary,
        float $monthlyHours,
        float $absenceHours
    ): array {
        $hourlyRate = $monthlySalary / $monthlyHours;

        $deduction = $hourlyRate * $absenceHours;

        return [
            'method' => 'heures_reelles',
            'hourly_rate' => round($hourlyRate, 2),
            'absence_hours' => $absenceHours,
            'deduction' => round($deduction, 2),
        ];
    }

    /**
     * Nombre de jours ouvrables/ouvrés dans le mois.
     */
    protected function countWorkingDaysInMonth(
        Carbon $date,
        bool $includeSaturday = true
    ): int {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        return $this->countDays(
            $start,
            $end,
            $includeSaturday
        );
    }

    /**
     * Nombre de jours ouvrables/ouvrés
     * sur une période.
     */
    protected function countWorkingDaysBetween(
        Carbon $startDate,
        Carbon $endDate,
        bool $includeSaturday = true
    ): int {
        return $this->countDays(
            $startDate,
            $endDate,
            $includeSaturday
        );
    }

    protected function countDays(
        Carbon $start,
        Carbon $end,
        bool $includeSaturday = true
    ): int {
        $count = 0;

        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            if ($includeSaturday) {
                // Exclut uniquement dimanche
                if (!$date->isSunday()) {
                    $count++;
                }
            } else {
                // Exclut samedi + dimanche
                if (!$date->isWeekend()) {
                    $count++;
                }
            }
        }

        return $count;
    }
}