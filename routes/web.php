<?php

use App\Http\Controllers\Web\Sales\SalesController;
use App\Http\Controllers\Web\Staff\StaffController;
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
    Route::get('sales/kanban', [SalesController::class, 'kanban'])->name('sale-kanban');
    Route::get('sales/create-lead', [SalesController::class, 'createLead'])->name('sales-create-lead');
    Route::post('sales/create-lead', [SalesController::class, 'storeLead']);
    Route::get('sales/all-list', [SalesController::class, 'allList'])->name('sales-all-list');
    Route::get('sales/lead-settings', [SalesController::class, 'leadSettings'])->name('sales-lead-settings');
    Route::get('sales/lead-view', [SalesController::class, 'leadView'])->name('sales-lead-view');

    // Staff Routes
    Route::get('staff/create', [StaffController::class, 'create'])->name('staff-create');
    Route::post('staff/create', [StaffController::class, 'store']);
    Route::get('staff/manage', [StaffController::class, 'manage'])->name('staff-manage');
    Route::get('staff/roles', [StaffController::class, 'roles'])->name('staff-roles');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
