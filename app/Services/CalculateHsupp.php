<?php

namespace App\Services;

use App\Enums\Impact;
use App\Enums\Periodicity;
use App\Enums\RemunerationElement;
use App\Enums\RemunerationType;
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
                $hsupps += $overtime->hours * $overtime->hours_rate * $overtime->multiplier;
            }

            if ($this->inDatabase) {
                if ($hsupps != 0) {
                    $this->employee->remunerations()->updateOrCreate(
                        ['name' => RemunerationElement::HEURE_SUPP->value],
                        [
                            'company_id' => $this->employee->company->id,
                            'name' => RemunerationElement::HEURE_SUPP->value,
                            'type' => RemunerationType::ALLOCATION->value,
                            'amount' => $hsupps,
                            'periodicity' => Periodicity::MONTHLY->value,
                            'impact' => Impact::TAXCOT->value,
                            'notes' => 'Total des heures supp. voir les details dans l\'onglet h supp',
                        ]
                    );
                }
            } else {
                return $hsupps;
            }
        }

        return 0;
    }
}
