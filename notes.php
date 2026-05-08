<?php

// ═══════════════════════════════════════════════════════════
//  EXEMPLES D'UTILISATION — Scopes chainables Employee / Company
// ═══════════════════════════════════════════════════════════

use App\Enums\ContractTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Company;
use App\Models\Employee;

$company = Company::find(1, 'id');


// ── 1. FILTRES SIMPLES ──────────────────────────────────────

// Tous les employés validés de la company
$company->employees()->validated()->get();

// Tous les employés sans payslip
$company->employees()->withoutPayslip()->get();

// Tous les employés dont le payslip est PENDING
$company->employees()->withPendingPayslip()->get();

// Par statut arbitraire
$company->employees()->ofStatus(StatusEnum::PENDING)->get();

// Par type de contrat
$company->employees()->ofContractType(ContractTypeEnum::CDI)->get();
// ou avec la string brute si l'enum n'est pas dispo partout :
$company->employees()->ofContractType('cdi')->get();


// ── 2. CHAÎNAGE ────────────────────────────────────────────

// Employés validés ET sans payslip
$company->employees()
    ->validated()
    ->withoutPayslip()
    ->get();

// Employés validés, hors stagiaires, sans payslip
$company->employees()
    ->validated()
    ->notInternship()
    ->withoutPayslip()
    ->get();

// Employés actifs (non résiliés) avec un payslip PENDING, hors stagiaires
$company->employees()
    ->active()
    ->notInternship()
    ->withPendingPayslip()
    ->get();

// CDI uniquement + validés + sans payslip
$company->employees()
    ->ofContractType(ContractTypeEnum::CDI)
    ->validated()
    ->withoutPayslip()
    ->get();

// Employés dont le payslip est à un statut précis
$company->employees()
    ->withPayslipStatus(StatusEnum::VALIDATED)
    ->get();


// ── 3. RACCOURCIS COMPANY ───────────────────────────────────

// Employés actifs (non résiliés)
$company->activeEmployees()->get();

// Employés éligibles à la paie (actifs + hors stagiaires)
$company->payrollEmployees()->get();

// Employés à qui il faut encore générer / valider un payslip
$company->employeesNeedingPayslip()->get();

// Raccourci + filtre supplémentaire
$company->payrollEmployees()->validated()->get();


// ── 4. SANS PASSER PAR COMPANY ─────────────────────────────

// Sur le modèle Employee directement (utile dans les policies, jobs, etc.)
Employee::validated()->withoutPayslip()->get();
Employee::ofContractType(ContractTypeEnum::CDD)->withPendingPayslip()->get();
Employee::active()->notInternship()->needsPayslip()->count();


// ── 5. AVEC D'AUTRES MÉTHODES ELOQUENT ─────────────────────

// Pagination
$company->employees()->validated()->withoutPayslip()->paginate(20);

// Premier résultat
$company->employees()->validated()->withoutPayslip()->first();

// Comptage
$company->employees()->active()->withoutPayslip()->count();

// Avec eager loading
$company->employees()->validated()->withoutPayslip()->with('salary')->get();

// Ordonné
$company->employees()->validated()->orderBy('name')->get();













<?php

// ── À ajouter dans routes/web.php ────────────────────────────────────────────
//
// use App\Http\Controllers\SubscriptionController;
//
// Route::middleware(['auth'])->group(function () {
//     Route::get( '/companies/{company}/subscription',        [SubscriptionController::class, 'index'])  ->name('subscription.index');
//     Route::post('/companies/{company}/subscription',        [SubscriptionController::class, 'store'])  ->name('subscription.store');
//     Route::delete('/companies/{company}/subscription',      [SubscriptionController::class, 'cancel']) ->name('subscription.cancel');
// });
//
// ─────────────────────────────────────────────────────────────────────────────
// Enregistrement de la policy dans AuthServiceProvider (ou AppServiceProvider) :
//
// use App\Models\Company;
// use App\Policies\SubscriptionPolicy;
//
// protected $policies = [
//     Company::class => SubscriptionPolicy::class,
// ];
//
// ─────────────────────────────────────────────────────────────────────────────
// Exemple d'utilisation de la policy dans un contrôleur d'employés :
//
// // EmployeeController.php
// public function store(Request $request, Company $company)
// {
//     $this->authorize('addEmployee', $company);   // ← bloque si quota atteint
//     // … créer l'employé
//     $this->subscriptions->consumeEmployeeSlot($company); // consomme 1 charge
// }
//
// public function update(Request $request, Company $company, Employee $employee)
// {
//     $this->authorize('manageEmployee', $company);
//     // …
// }
//
// ─────────────────────────────────────────────────────────────────────────────
// Exemple Blade :
//
// @can('addEmployee', $company)
//     <a href="{{ route('employees.create', $company) }}" class="btn btn-primary">
//         Ajouter un employé
//     </a>
// @else
//     <p class="text-red-500">Quota d'employés atteint — veuillez mettre à niveau votre plan.</p>
// @endcan