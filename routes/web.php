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
    Route::post('sales/create-lead', [SalesController::class, 'storeLead'])->name('sales-leads.store');
    Route::get('sales/leads/{lead}/edit', [SalesController::class, 'editLead'])->name('sales-leads.edit');
    Route::put('sales/leads/{lead}', [SalesController::class, 'updateLead'])->name('sales-leads.update');
    Route::delete('sales/leads/{lead}', [SalesController::class, 'destroyLead'])->name('sales-leads.destroy');
    Route::post('sales/leads/{lead}/activities', [SalesController::class, 'storeLeadActivity'])->name('sales-lead-activities.store');
    Route::put('sales/leads/{lead}/activities/{activity}', [SalesController::class, 'updateLeadActivity'])->name('sales-lead-activities.update');
    Route::delete('sales/leads/{lead}/activities/{activity}', [SalesController::class, 'destroyLeadActivity'])->name('sales-lead-activities.destroy');
    Route::post('sales/leads/{lead}/activities/{activity}/complete', [SalesController::class, 'completeLeadActivity'])->name('sales-lead-activities.complete');
    Route::get('sales/leads/{lead}/activity-fragments', [SalesController::class, 'leadActivityFragments'])->name('sales-lead-activities.fragments');
    Route::get('sales/all-list', [SalesController::class, 'allList'])->name('sales-all-list');
    Route::get('sales/lead-settings', [SalesController::class, 'leadSettings'])->name('sales-lead-settings');
    Route::post('sales/lead-settings', [SalesController::class, 'storeLeadSetting'])->name('sales-lead-settings.store');
    Route::put('sales/lead-settings/{leadSetting}', [SalesController::class, 'updateLeadSetting'])->name('sales-lead-settings.update');
    Route::delete('sales/lead-settings/{leadSetting}', [SalesController::class, 'destroyLeadSetting'])->name('sales-lead-settings.destroy');
    Route::get('sales/lead-view/{lead}', [SalesController::class, 'leadView'])->name('sales-lead-view');

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
