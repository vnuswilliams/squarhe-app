<?php

use App\Jobs\SendEmployeeBirthdayNotificationsJob;
use App\Jobs\SendEmployeeContractExpiryNotificationsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendEmployeeBirthdayNotificationsJob)->dailyAt('00:00');
Schedule::job(new SendEmployeeContractExpiryNotificationsJob)->dailyAt('00:00');
