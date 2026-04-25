<?php

namespace App\Services;

use App\Enums\ImpactEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Models\Employee;

/**
 * Calculate des overtimes and put it the remunerations table
 *
 * @var Employee $employee the employee who you'll calculate overtimes
 */
class CalculateHsupp
{
    /**
     * Create a new job instance.
     */
    public function __construct(public Employee $employee, public bool $inDatabase = false) {}

    public function handle()
    {

        $ifovertimes = $this->employee->overtimes;

        if ($ifovertimes->isNotEmpty()) {
            $hsupps = 0;

            // Récuperation des hsupp et envoie dans la table elemnts
            foreach ($ifovertimes as $overtime) {
                $hsupps += $overtime->alloc;
            }

            if ($this->inDatabase) {
                if ($hsupps != 0) {
                    $this->employee->remunerations()->updateOrCreate(
                        ['name' => RemunerationEnum::HEURE_SUPP->value],
                        [
                            'name' => RemunerationEnum::HEURE_SUPP->value,
                            'type' => RemunerationTypeEnum::ALLOCATION->value,
                            'amount' => $hsupps,
                            'periodicity' => PeriodicityEnum::UNIQUE->value,
                            'impact' => ImpactEnum::TAXCOT->value,
                            'notes' => 'Total des heures supp.',
                        ]
                    );
                }
            } else {
                return $hsupps;
            }
        }

        return 0;
    }

    public function hourRate($employee)
    {
        $smic = $employee->data['smic'];
        $addon = $employee->remunerations()
            ->whereIn('name', [RemunerationEnum::PRIME_TECHNICITE, RemunerationEnum::PRIME_RENDEMENT, RemunerationEnum::PRIME_FONCTION])
            ->whereNotIn('name', [RemunerationEnum::PRIME_PANIER, RemunerationEnum::PRIME_ANCIENNETE, RemunerationEnum::INDEMNITE_LOGEMENT, RemunerationEnum::INDEMNITE_DEPLACEMENT, RemunerationEnum::INDEMNITE_TRANSPORT,             RemunerationEnum::PRIME_OUTILLAGE, RemunerationEnum::PRIME_ASSIDUITE])
            ->sum('amount');
        $rate = ($smic + $addon) / $employee->company->data['labourHours'];
        return number_format($rate, 2, '.', '');
    }
}
