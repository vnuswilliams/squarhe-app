<?php

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

Route::livewire('/notifications', 'pages::notifs.notifications')->name('notif');
Route::livewire('/employees', 'pages::employees.employee')->name('employees');
Route::livewire('/employees/add', 'pages::employees.add-employee')->name('employees.add');
Route::livewire('/employees/import', 'pages::employees.employee-import')->name('employees.import');
   Route::livewire('employee/profil/{uuid:uuid}', 'pages::employees.employee-profil')->name('employees.show');
    
});


Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        Cookie::queue('locale', $locale, 60 * 24 * 365);
    }
    return back();
});
Route::passkeys();

require __DIR__.'/settings.php';
