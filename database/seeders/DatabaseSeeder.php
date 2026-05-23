<?php

namespace Database\Seeders;

use App\Enums\CivilityEnum;
use App\Enums\ContractTypeEnum;
use App\Enums\DepartmentEnum;
use App\Enums\ImpactEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\NationalityEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Remuneration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        /*$this->call([
            RolePermissionSeeder::class,
            FeatureSeeder::class,
            PlanSeeder::class,
            // SubscriptionRolesAndPermissionsSeeder::class,
            //CompanySeeder::class,
            // other seeders can be added here if needed
        ]);*/
        /*    for ($i=0; $i < 150; $i++) {
            # code...
        Employee::create([
            'company_id' => 1,
            'name'  => fake()->name(),
            'status' => fake()->randomElement(StatusEnum::values()),
            'department' => fake()->randomElement(DepartmentEnum::values())  ,
            'job_title' => fake()->jobTitle(),
            'contract_type' => fake()->randomElement(ContractTypeEnum::values()),
            'start_date' => fake()->dateTimeBetween('2010-01-01', '2022-01-26')
            ->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('2023-01-01', '2035-01-26')
            ->format('Y-m-d'),
            'base_salary' => fake()->numberBetween(50000, 2500000),
            'data'=> [
                'birth_date' => fake()->dateTimeBetween('1995-01-01', '2003-01-26')
                ->format('Y-m-d'),
                'nationality'=> fake()->randomElement(NationalityEnum::values()),
                'civility'=> fake()->randomElement(CivilityEnum::values()),
                'phone' => fake()->phoneNumber(),
                'child' => fake()->numberBetween(1, 5),
                'email' => fake()->email(),
                'category' => fake()->optional(0.7)->randomElement(['1A', '12A' ,'4D', '6F', '9B']),
              
                'average_salary'=> fake()->numberBetween(50000, 1500000),
                'smic' => 75000,
                'syndicat' => false
            ]
        ]);
        }  
       for ($i=0; $i < 150; $i++) {
            # code...
        Remuneration::create([
            'ref' => "05-2026",
            'employee_id' => "019df8db-3385-73b3-b444-da7f2a412bab",
            'name' => fake()->randomElement(RemunerationEnum::forSelect()) ,
            'type' => fake()->randomElement(RemunerationTypeEnum::values()),
            'amount' => fake()->randomNumber() ,
            'periodicity' => fake()->randomElement(PeriodicityEnum::values()) ,
            'impact' => fake()->randomElement(ImpactEnum::values()) ,
            'added_by' => "vnuswilliams",
            'notes' => fake()->text(100) ,
        ]);

        }
        for ($i = 0; $i < 500; $i++) {
            // code...
            Leave::create([
                'ref' => '05-2026',
                'employee_id' => fake()->randomElement(Employee::whereCompanyId(1)->get()->pluck('id')->toArray()),
                'type' => fake()->randomElement(LeaveTypeEnum::forselect()),

                'start_date' => fake()->dateTimeBetween('2025-01-01', '2026-01-26')
                ->format('Y-m-d'),
                'end_date' => fake()->dateTimeBetween('2026-01-26', '2026-12-31')
                ->format('Y-m-d'),
                'days' => fake()->numberBetween(1, 30),
                'status' => fake()->randomElement(StatusEnum::values()),
                'notes' => fake()->optional(0.4) ->text(100),
                'approved_by' => 'vnuswilliams',
                'added_by' => 'vnuswilliams',
                'approbation_date' => now(),

            ]);
        }*/
        /*User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);*/
    }
}
