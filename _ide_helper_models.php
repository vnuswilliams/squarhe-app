<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property \App\Enums\RemunerationEnum $name
 * @property string $amount
 * @property int $limit_fisc
 * @property int $excedent
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereExcedent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereLimitFisc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdvNat whereUpdatedAt($value)
 */
	class AdvNat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $adresse
 * @property string $city
 * @property string|null $nui
 * @property string|null $cnps
 * @property string|null $rccm
 * @property string $join_code
 * @property array<array-key, mixed> $data
 * @property string|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAdresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCnps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereJoinCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereNui($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereRccm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $employee_id
 * @property string|null $motif
 * @property string $department
 * @property string $job_title
 * @property string $contract_type
 * @property string $start_date
 * @property string $end_date
 * @property numeric $base_salary
 * @property numeric $smic
 * @property numeric $average_salary
 * @property string|null $category
 * @property string|null $added_by
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereAverageSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereBaseSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereContractType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereMotif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereSmic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContractArchive whereUpdatedAt($value)
 */
	class ContractArchive extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $employee_id
 * @property \App\Enums\DocumentTypeEnum $type
 * @property string $name
 * @property string|null $notes
 * @property string $path
 * @property string $added_by
 * @property \App\Enums\DocumentAccessEnum $access
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUuid($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int|null $company_id
 * @property string $name
 * @property string|null $status
 * @property string $department
 * @property string $job_title
 * @property string $contract_type
 * @property \Carbon\CarbonImmutable $start_date
 * @property \Carbon\CarbonImmutable|null $end_date
 * @property int $base_salary
 * @property array<array-key, mixed>|null $data
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdvNat> $advnats
 * @property-read int|null $advnats_count
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContractArchive> $contractArchives
 * @property-read int|null $contract_archives_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\EmployeeContribution|null $employeeContributions
 * @property-read \App\Models\EmployerContribution|null $employerContributions
 * @property-read mixed $anc
 * @property-read mixed $bday
 * @property-read mixed $category
 * @property-read mixed $child
 * @property-read mixed $civility
 * @property-read mixed $cnps
 * @property-read mixed $email
 * @property-read mixed $nationality
 * @property-read mixed $niu
 * @property-read mixed $phone
 * @property-read mixed $short_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Iran> $irans
 * @property-read int|null $irans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Leave> $leaves
 * @property-read int|null $leaves_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Overtime> $overtimes
 * @property-read int|null $overtimes_count
 * @property-read \App\Models\Payslip|null $payslip
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Remuneration> $remunerations
 * @property-read int|null $remunerations_count
 * @property-read \App\Models\Salary|null $salary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBaseSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereContractType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUuid($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property int $old_age_pension
 * @property int $irpp
 * @property int $cac
 * @property int $cfc
 * @property int $syndicat
 * @property int $rav
 * @property int $tdl
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read mixed $total
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereCac($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereCfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereIrpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereOldAgePension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereRav($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereSyndicat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereTdl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeContribution whereUpdatedAt($value)
 */
	class EmployeeContribution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property int $family_allowance
 * @property int $old_age_pension
 * @property int $accident
 * @property int $cfc
 * @property int $fne
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read mixed $total
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereAccident($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereCfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereFamilyAllowance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereFne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereOldAgePension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployerContribution whereUpdatedAt($value)
 */
	class EmployerContribution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property \App\Enums\RemunerationEnum $name
 * @property int $amount
 * @property int $limit_fisc
 * @property int $quote
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereLimitFisc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereQuote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Iran whereUpdatedAt($value)
 */
	class Iran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property \App\Enums\LeaveTypeEnum $type
 * @property \Carbon\CarbonImmutable $start_date
 * @property \Carbon\CarbonImmutable $end_date
 * @property int $days
 * @property \App\Enums\StatusEnum|null $status
 * @property string|null $notes
 * @property string $approved_by
 * @property string $approbation_date
 * @property \Carbon\CarbonImmutable|null $last_leave
 * @property int $leaves_balance
 * @property int $leaves_majority
 * @property int $leaves_seniority
 * @property int $leaves_child
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereApprobationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLastLeave($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLeavesBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLeavesChild($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLeavesMajority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLeavesSeniority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereUpdatedAt($value)
 */
	class Leave extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property int $week
 * @property \App\Enums\HsuppEnum $day_type
 * @property float $hours
 * @property float $hours_rate
 * @property float $multiplier
 * @property float $alloc
 * @property string $added_by
 * @property string|null $notes
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereAlloc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereDayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereHoursRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereWeek($value)
 */
	class Overtime extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property array<array-key, mixed>|null $employee_data
 * @property array<array-key, mixed>|null $company_data
 * @property \App\Enums\StatusEnum|null $status
 * @property array<array-key, mixed>|null $elements_data
 * @property array<array-key, mixed>|null $employee_contribution
 * @property array<array-key, mixed>|null $employer_contribution
 * @property array<array-key, mixed>|null $retenues_data
 * @property array<array-key, mixed>|null $salaries_data
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read mixed $formatted_contributions
 * @property-read mixed $formatted_salaries
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereCompanyData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereElementsData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereEmployeeContribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereEmployeeData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereEmployerContribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereRetenuesData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereSalariesData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payslip whereUpdatedAt($value)
 */
	class Payslip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property \App\Enums\RemunerationEnum $name
 * @property \App\Enums\RemunerationTypeEnum $type
 * @property int $amount
 * @property \App\Enums\PeriodicityEnum $periodicity
 * @property \App\Enums\ImpactEnum $impact
 * @property string|null $added_by
 * @property string|null $notes
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration sumByName()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration wherePeriodicity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remuneration whereUpdatedAt($value)
 */
	class Remuneration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $ref
 * @property int $employee_id
 * @property int $base_salary
 * @property int $gross_salary
 * @property int $intermediate_taxable_gross_salary
 * @property int $taxable_gross_salary
 * @property int $contributory_salary
 * @property int $average_salary
 * @property int $smic
 * @property int $contributions
 * @property int $retenues
 * @property int $nap
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereAverageSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereBaseSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereContributions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereContributorySalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereIntermediateTaxableGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereNap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereRetenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereSmic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereTaxableGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereUpdatedAt($value)
 */
	class Salary extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\LaravelPasskeys\Models\Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 */
	class User extends \Eloquent implements \Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys {}
}

