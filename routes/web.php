<?php

use App\Http\Controllers\AcceptInvitationController;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('/notifications', 'pages::notifs.notifications')->name('notif');
    Route::livewire('/employees', 'pages::employees.employee')->name('employees');
    Route::livewire('/documents', 'pages::documents.document')->name('documents');
    Route::livewire('/metrics', 'pages::metrics.metrics')->name('metrics');
    Route::livewire('/support', 'pages::support.support')->name('support');
    Route::livewire('/pay', 'pages::payroll.pay')->name('pay');
    Route::livewire('/pay/check/payslips', 'pages::payroll.check-payslip')->name('pay.check.payslips');
    Route::livewire('/pay/payroll/close', 'pages::payroll.close-payroll')->name('pay.close.payroll');

    Route::livewire('/leaves', 'pages::leaves.leaves')->name('leaves');

    Route::livewire('/employees/add', 'pages::employees.add-employee')->name('employees.add');
    Route::livewire('/employees/import', 'pages::employees.employee-import')->name('employees.import');
    Route::livewire('/employees/import/overtimes', 'pages::employees.company-overtimes-import')->name('employees.import.overtimes');
    Route::livewire('/employees/import/leaves', 'pages::employees.company-leaves-import')->name('employees.import.leaves');
    Route::livewire('/employees/import/remunerations', 'pages::employees.company-remunerations-import')->name('employees.import.remunerations');
    Route::livewire('/employee/profil/{id}', 'pages::employees.employee-profil')->name('employees.show');
    Route::livewire('/employee/finish/contract', 'pages::employees.employee-end-contract')->name('employees.end.contract');

    // web.php
    Route::get('/invitation/accept/{company_code}/{invitation}', AcceptInvitationController::class)->name('invitation.accept');
});

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        Cookie::queue('locale', $locale, 60 * 24 * 365);
    }
    return back();
});
Route::passkeys();

require __DIR__ . '/settings.php';
