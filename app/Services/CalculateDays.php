<?php

namespace App\Services;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;


class CalculateDays {

    /**
     * Determinate the numbers of days between $startDate and $endDate
     * @param mixed $startDate the start date (yyyy-mm-dd)
     * @param mixed $endDate the end date (yyyy-mm-dd)
     * @return int
     */
    public function calculateDays(string | Datetime|null $startDate, string | Datetime|null $endDate): int
    {
        if (
            !($startDate instanceof DateTime) && !is_string($startDate) ||
            !($endDate instanceof DateTime) && !is_string($endDate) ||
            (is_string($startDate) && trim($startDate) === '') ||
            (is_string($endDate) && trim($endDate) === '')
        ) {
            return 0;
        }
       
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Jours fériés fixes (format 'm-d')
        $fixedHolidays = auth()->user()->companies()->first()->companySetting->data['fixedHolidays'] ?? config('squarhe.fixedHolidays');

        $days = 0;
        // Utiliser CarbonPeriod::create qui est inclusif par défaut.
        // C'est plus simple et corrige le problème de `daysUntil`.
        $period = CarbonPeriod::create($start, $end)->excludeEndDate();

        $holidaysByYear = [];

        foreach ($period as $date) {
            if ($date->isSunday()) {
                continue;
            }

            $year = $date->year;

            // Calculer les jours fériés mobiles pour l'année si pas déjà fait
            if (!isset($holidaysByYear[$year])) {
                $easter = Carbon::createFromTimestamp(easter_date($year))->setTimezone($date->getTimezone());
                $holidaysByYear[$year] = [
                    $easter->copy()->subDays(2)->format('m-d'),  // Vendredi Saint
                    $easter->copy()->addDays(39)->format('m-d'), // Ascension
                ];
            }

            $allHolidaysForYear = array_merge($fixedHolidays, $holidaysByYear[$year]);

            if (in_array($date->format('m-d'), $allHolidaysForYear)) {
                continue;
            }

            // Gérer le cas du lundi de report si un férié tombe un dimanche
            if ($date->isMonday()) {
                $yesterday = $date->copy()->subDay();
                if ($yesterday->isSunday()) {
                    $yesterdayYear = $yesterday->year;
                    if (!isset($holidaysByYear[$yesterdayYear])) {
                        $easter = Carbon::createFromTimestamp(easter_date($yesterdayYear))->setTimezone($date->getTimezone());
                        $holidaysByYear[$yesterdayYear] = [
                            $easter->copy()->subDays(2)->format('m-d'),
                            $easter->copy()->addDays(39)->format('m-d'),
                        ];
                    }
                    $allHolidaysForYesterdayYear = array_merge($fixedHolidays, $holidaysByYear[$yesterdayYear]);
                    if (in_array($yesterday->format('m-d'), $allHolidaysForYesterdayYear)) {
                        continue; // Ce lundi est chômé
                    }
                }
            }

            $days++;
        }

        return $days;
    }
}