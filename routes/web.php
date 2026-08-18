<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Sales Routes
    Volt::route('sales/kanban', 'sales.kanban')->name('sale-kanban');
    Volt::route('sales/create-lead', 'sales.create-lead')->name('sales-create-lead');
    Volt::route('sales/all-list', 'sales.all-list')->name('sales-all-list');
    Volt::route('sales/lead-settings', 'sales.lead-settings')->name('sales-lead-settings');

    // Staff Routes
    Volt::route('staff/create', 'staff.create')->name('staff-create');
    Volt::route('staff/manage', 'staff.manage')->name('staff-manage');
    Volt::route('staff/roles', 'staff.roles')->name('staff-roles');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
