<?php

namespace Database\Seeders;

use App\Enums\ImpactEnum;
use App\Enums\LeaveTypeEnum;
use App\Enums\PeriodicityEnum;
use App\Enums\RemunerationEnum;
use App\Enums\RemunerationTypeEnum;
use App\Enums\StatusEnum;
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
        /*for ($i=0; $i < 150; $i++) {
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
        for ($i = 0; $i < 150; $i++) {
            // code...
            Leave::create([
                'ref' => '05-2026',
                'employee_id' => '019df8db-3385-73b3-b444-da7f2a412bab',
                'type' => fake()->randomElement(LeaveTypeEnum::values()),

                'start_date' => fake()->date(),
                'end_date' => fake()->date(),
                'days' => fake()->randomNumber(),
                'status' => fake()->randomElement(StatusEnum::values()),
                'notes' => fake()->text(100),
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
