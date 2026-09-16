<?php

use App\Http\Controllers\Api\Webhook\LeadWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\Integration\ApiTokenController;
use App\Http\Controllers\Web\Sales\SalesController;
use App\Http\Controllers\Web\Staff\StaffController;
use App\Http\Middleware\AuthenticateWebhookApiToken;
use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureLeadAccess;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::post('webhook/v1/lead/create', [LeadWebhookController::class, 'store'])
    ->middleware(AuthenticateWebhookApiToken::class)
    ->name('webhook.v1.lead.create');

Route::post('api/webhook/v1/lead/create', [LeadWebhookController::class, 'store'])
    ->middleware(AuthenticateWebhookApiToken::class);

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', EnsureCompanyOnboardingComplete::class, 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('locations/countries', [SalesController::class, 'locationCountries'])->name('locations.countries');
    Route::get('locations/states', [SalesController::class, 'locationStates'])->name('locations.states');
    Route::get('locations/cities', [SalesController::class, 'locationCities'])->name('locations.cities');
    Volt::route('company/onboarding', 'company.onboarding')->name('company.onboarding');
});

Route::middleware(['auth', EnsureCompanyOnboardingComplete::class, 'verified'])->group(function () {
    Route::get('calendar', [CalendarController::class, 'index'])->middleware(EnsureLeadAccess::class)->name('calendar');
    Route::get('calendar/events', [CalendarController::class, 'events'])->middleware(EnsureLeadAccess::class)->name('calendar.events');
    // Sales Routes
    Route::prefix('sales')->middleware(EnsureLeadAccess::class)->group(function () {
        Route::get('kanban', [SalesController::class, 'kanban'])->name('sale-kanban');
        Route::get('leads/kanban-data', [SalesController::class, 'kanbanLeads'])->name('sales-leads.kanban-data');
        Route::patch('leads/{lead}/status', [SalesController::class, 'updateKanbanStatus'])->name('sales-leads.status');
        Route::get('leads/{lead}/kanban-details', [SalesController::class, 'kanbanLeadDetails'])->name('sales-leads.kanban-details');
        Route::patch('leads/{lead}/assignee', [SalesController::class, 'assignKanbanLead'])->name('sales-leads.assignee');
        Route::get('create-lead', [SalesController::class, 'createLead'])->name('sales-create-lead');
        Route::post('create-lead', [SalesController::class, 'storeLead'])->name('sales-leads.store');
        Route::get('leads/{lead}/edit', [SalesController::class, 'editLead'])->name('sales-leads.edit');
        Route::put('leads/{lead}', [SalesController::class, 'updateLead'])->name('sales-leads.update');
        Route::delete('leads/{lead}', [SalesController::class, 'destroyLead'])->name('sales-leads.destroy');
        Route::post('leads/import', [SalesController::class, 'importLeads'])->name('sales-leads.import');
        Route::get('leads/import/sample', [SalesController::class, 'downloadLeadImportSample'])->name('sales-leads.import.sample');
        Route::get('leads/export', [SalesController::class, 'exportLeads'])->name('sales-leads.export');
        Route::post('leads/{lead}/activities', [SalesController::class, 'storeLeadActivity'])->name('sales-lead-activities.store');
        Route::put('leads/{lead}/activities/{activity}', [SalesController::class, 'updateLeadActivity'])->name('sales-lead-activities.update');
        Route::delete('leads/{lead}/activities/{activity}', [SalesController::class, 'destroyLeadActivity'])->name('sales-lead-activities.destroy');
        Route::post('leads/{lead}/activities/{activity}/complete', [SalesController::class, 'completeLeadActivity'])->name('sales-lead-activities.complete');
        Route::get('leads/{lead}/activity-fragments', [SalesController::class, 'leadActivityFragments'])->name('sales-lead-activities.fragments');
        Route::get('all-list', [SalesController::class, 'allList'])->name('sales-all-list');
        Route::get('lead-settings', [SalesController::class, 'leadSettings'])->name('sales-lead-settings');
        Route::post('lead-settings', [SalesController::class, 'storeLeadSetting'])->name('sales-lead-settings.store');
        Route::put('lead-settings/{leadSetting}', [SalesController::class, 'updateLeadSetting'])->name('sales-lead-settings.update');
        Route::delete('lead-settings/{leadSetting}', [SalesController::class, 'destroyLeadSetting'])->name('sales-lead-settings.destroy');
        Route::get('lead-view/{lead}', [SalesController::class, 'leadView'])->name('sales-lead-view');
    });

    // Staff Routes
    Route::prefix('staff')->group(function () {
        Route::get('create', [StaffController::class, 'create'])->middleware(EnsurePermission::class.':create_staff')->name('staff-create');
        Route::post('create', [StaffController::class, 'store'])->middleware(EnsurePermission::class.':create_staff')->name('staff.store');
        Route::get('manage', [StaffController::class, 'manage'])->middleware(EnsurePermission::class.':view_staff')->name('staff-manage');
        Route::get('{staffUser}/edit', [StaffController::class, 'edit'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.edit');
        Route::put('{staffUser}', [StaffController::class, 'update'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.update');
        Route::post('{staffUser}/resend-password', [StaffController::class, 'resendPassword'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.resend-password');
        Route::delete('{staffUser}', [StaffController::class, 'destroy'])->middleware(EnsurePermission::class.':delete_staff')->name('staff.destroy');
        Route::middleware(EnsurePermission::class.':manage_roles')->group(function () {
            Route::get('roles', [StaffController::class, 'roles'])->name('staff-roles');
            Route::post('roles', [StaffController::class, 'storeRole'])->name('staff.roles.store');
            Route::get('roles/{role}/edit', [StaffController::class, 'editRole'])->name('staff.roles.edit');
            Route::put('roles/{role}', [StaffController::class, 'updateRole'])->name('staff.roles.update');
        });
    });

    // Integration routes
    Route::prefix('integration')->group(function () {
        Route::get('api-token', [ApiTokenController::class, 'index'])->name('integration-api-token');
        Route::post('api-token', [ApiTokenController::class, 'store'])->name('integration-api-token.store');
        Route::get('google-sheet', [ApiTokenController::class, 'googleSheet'])->name('integration-google-sheet');
    });

    // Settings Routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
