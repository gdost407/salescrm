<?php

use App\Actions\SalesReport;
use App\Http\Controllers\Api\Webhook\LeadWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\Client\ClientController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Integration\ApiTokenController;
use App\Http\Controllers\Web\Invoice\InvoiceController;
use App\Http\Controllers\Web\Item\ItemController;
use App\Http\Controllers\Web\Job\JobController;
use App\Http\Controllers\Web\Ledger\LedgerController;
use App\Http\Controllers\Web\Payment\PaymentController;
use App\Http\Controllers\Web\Quotation\QuotationController;
use App\Http\Controllers\Web\Report\ReportController;
use App\Http\Controllers\Web\Sales\SalesController;
use App\Http\Controllers\Web\Staff\AttendanceController;
use App\Http\Controllers\Web\Staff\StaffController;
use App\Http\Controllers\Web\Tax\TaxController;
use App\Http\Middleware\AuthenticateWebhookApiToken;
use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Http\Middleware\EnsureLeadAccess;
use App\Http\Middleware\EnsurePermission;
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

Route::get('dashboard', [DashboardController::class, 'index'])
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
    Route::get('reports/{type}', [ReportController::class, 'index'])
        ->whereIn('type', array_keys(SalesReport::MODULES))->name('reports.index');
    Route::get('reports/{type}/export', [ReportController::class, 'export'])
        ->whereIn('type', array_keys(SalesReport::MODULES))->name('reports.export');
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

    Route::resource('clients', ClientController::class)->parameters(['clients' => 'client'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_clients');
    Route::resource('clients', ClientController::class)->parameters(['clients' => 'client'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_clients');
    Route::resource('clients', ClientController::class)->parameters(['clients' => 'client'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_clients');
    Route::resource('clients', ClientController::class)->parameters(['clients' => 'client'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_clients');

    Route::resource('taxes', TaxController::class)->parameters(['taxes' => 'tax'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_taxes');
    Route::resource('taxes', TaxController::class)->parameters(['taxes' => 'tax'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_taxes');
    Route::resource('taxes', TaxController::class)->parameters(['taxes' => 'tax'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_taxes');
    Route::resource('taxes', TaxController::class)->parameters(['taxes' => 'tax'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_taxes');

    Route::get('items/{item}/image', [ItemController::class, 'image'])->name('items.image');
    Route::resource('items', ItemController::class)->parameters(['items' => 'item'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_catalog_items');
    Route::resource('items', ItemController::class)->parameters(['items' => 'item'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_catalog_items');
    Route::resource('items', ItemController::class)->parameters(['items' => 'item'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_catalog_items');
    Route::resource('items', ItemController::class)->parameters(['items' => 'item'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_catalog_items');

    Route::get('catalog-items/{item}/image', [ItemController::class, 'image'])->name('catalog-items.image');
    Route::resource('catalog-items', ItemController::class)->parameters(['catalog-items' => 'item'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_catalog_items');
    Route::resource('catalog-items', ItemController::class)->parameters(['catalog-items' => 'item'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_catalog_items');
    Route::resource('catalog-items', ItemController::class)->parameters(['catalog-items' => 'item'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_catalog_items');
    Route::resource('catalog-items', ItemController::class)->parameters(['catalog-items' => 'item'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_catalog_items');

    Route::get('quotations/{document}/print', [QuotationController::class, 'print'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':view_quotations')->name('quotations.print');
    Route::get('quotations/{document}/attachments/{attachment}', [QuotationController::class, 'attachment'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':view_quotations')->name('quotations.attachment');
    Route::get('quotations', [QuotationController::class, 'index'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':view_quotations')->name('quotations.index');
    Route::get('quotations/create', [QuotationController::class, 'create'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':create_quotations')->name('quotations.create');
    Route::post('quotations', [QuotationController::class, 'store'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':create_quotations')->name('quotations.store');
    Route::get('quotations/{document}/edit', [QuotationController::class, 'edit'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':edit_quotations')->name('quotations.edit');
    Route::match(['put', 'patch'], 'quotations/{document}', [QuotationController::class, 'update'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':edit_quotations')->name('quotations.update');
    Route::get('quotations/{document}', [QuotationController::class, 'show'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':view_quotations')->name('quotations.show');
    Route::delete('quotations/{document}', [QuotationController::class, 'destroy'])->defaults('document_type', 'quotation')->middleware(EnsurePermission::class.':delete_quotations')->name('quotations.destroy');

    Route::get('jobs/{document}/print', [JobController::class, 'print'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':view_jobs')->name('jobs.print');
    Route::get('jobs/{document}/attachments/{attachment}', [JobController::class, 'attachment'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':view_jobs')->name('jobs.attachment');
    Route::get('jobs', [JobController::class, 'index'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':view_jobs')->name('jobs.index');
    Route::get('jobs/create', [JobController::class, 'create'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':create_jobs')->name('jobs.create');
    Route::post('jobs', [JobController::class, 'store'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':create_jobs')->name('jobs.store');
    Route::get('jobs/{document}/edit', [JobController::class, 'edit'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':edit_jobs')->name('jobs.edit');
    Route::match(['put', 'patch'], 'jobs/{document}', [JobController::class, 'update'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':edit_jobs')->name('jobs.update');
    Route::get('jobs/{document}', [JobController::class, 'show'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':view_jobs')->name('jobs.show');
    Route::delete('jobs/{document}', [JobController::class, 'destroy'])->defaults('document_type', 'job')->middleware(EnsurePermission::class.':delete_jobs')->name('jobs.destroy');

    Route::get('invoices/{document}/print', [InvoiceController::class, 'print'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':view_invoices')->name('invoices.print');
    Route::get('invoices/{document}/attachments/{attachment}', [InvoiceController::class, 'attachment'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':view_invoices')->name('invoices.attachment');
    Route::get('invoices', [InvoiceController::class, 'index'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':view_invoices')->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':create_invoices')->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':create_invoices')->name('invoices.store');
    Route::get('invoices/{document}/edit', [InvoiceController::class, 'edit'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':edit_invoices')->name('invoices.edit');
    Route::match(['put', 'patch'], 'invoices/{document}', [InvoiceController::class, 'update'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':edit_invoices')->name('invoices.update');
    Route::get('invoices/{document}', [InvoiceController::class, 'show'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':view_invoices')->name('invoices.show');
    Route::delete('invoices/{document}', [InvoiceController::class, 'destroy'])->defaults('document_type', 'invoice')->middleware(EnsurePermission::class.':delete_invoices')->name('invoices.destroy');

    Route::get('payments/{payment}/print', [PaymentController::class, 'print'])->middleware(EnsurePermission::class.':view_payments')->name('payments.print');
    Route::resource('payments', PaymentController::class)->parameters(['payments' => 'payment'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_payments');
    Route::resource('payments', PaymentController::class)->parameters(['payments' => 'payment'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_payments');
    Route::resource('payments', PaymentController::class)->parameters(['payments' => 'payment'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_payments');
    Route::resource('payments', PaymentController::class)->parameters(['payments' => 'payment'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_payments');

    Route::resource('ledger', LedgerController::class)->parameters(['ledger' => 'ledger'])->only(['create', 'store'])->middleware(EnsurePermission::class.':create_ledger');
    Route::resource('ledger', LedgerController::class)->parameters(['ledger' => 'ledger'])->only(['edit', 'update'])->middleware(EnsurePermission::class.':edit_ledger');
    Route::resource('ledger', LedgerController::class)->parameters(['ledger' => 'ledger'])->only(['index', 'show'])->middleware(EnsurePermission::class.':view_ledger');
    Route::resource('ledger', LedgerController::class)->parameters(['ledger' => 'ledger'])->only(['destroy'])->middleware(EnsurePermission::class.':delete_ledger');

    // Staff Routes
    Route::prefix('staff')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('staff.attendance.index');
        Route::post('attendance/punch', [AttendanceController::class, 'punch'])->name('staff.attendance.punch');
        Route::get('create', [StaffController::class, 'create'])->middleware(EnsurePermission::class.':create_staff')->name('staff-create');
        Route::post('create', [StaffController::class, 'store'])->middleware(EnsurePermission::class.':create_staff')->name('staff.store');
        Route::get('manage', [StaffController::class, 'manage'])->middleware(EnsurePermission::class.':view_staff')->name('staff-manage');
        Route::get('{staffUser}/edit', [StaffController::class, 'edit'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.edit');
        Route::put('{staffUser}', [StaffController::class, 'update'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.update');
        Route::post('{staffUser}/resend-password', [StaffController::class, 'resendPassword'])->middleware(EnsurePermission::class.':edit_staff')->name('staff.resend-password');
        Route::delete('{staffUser}', [StaffController::class, 'destroy'])->middleware(EnsurePermission::class.':delete_staff')->name('staff.destroy');
        Route::middleware(EnsurePermission::class.':edit_staff')->group(function () {
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
