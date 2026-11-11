<?php

use App\Http\Controllers\Auth\Admin\ConfirmPasswordController;
use App\Http\Controllers\Auth\Admin\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\Collections\ReceiptController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\HOACenterController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\HomeownerController;
use App\Http\Controllers\Admin\ManualController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PrintDocumentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResidentValidIdController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\Rbi\RbiFormController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for the THHA admin panel. They are loaded by the
| RouteServiceProvider within the "web" middleware group, prefixed
| with "/admin".
*/

Route::middleware('guest')->group(function () {

    // ---------------------------------------------------------------
    // Registration
    // ---------------------------------------------------------------

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
        ->middleware('registration.open:admin')
        ->name('admin.register');
    Route::post('register', [RegisterController::class, 'register'])
        ->middleware('registration.open:admin')
        ->name('admin.register.store');
});

Route::middleware([
    'auth',
    'account.active:web',
    'password.expiry:web,settings.profile'
])->group(function () {

    // ---------------------------------------------------------------
    // Authentication
    // ---------------------------------------------------------------

    // Logout
    Route::post('logout', LogoutController::class)->name('admin.logout');

    // Password Confirm
    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    // ---------------------------------------------------------------
    // Home
    // ---------------------------------------------------------------

    Route::get('/', [HomeController::class, 'index'])->name('admin.home');

    // ---------------------------------------------------------------
    // User Manual
    // ---------------------------------------------------------------

    Route::get('/manual/{topic?}', [ManualController::class, 'index'])
        ->middleware('manual.locale')->name('admin.manual');

    // ---------------------------------------------------------------
    // Notifications
    // ---------------------------------------------------------------

    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('admin.notifications.feed');
    Route::get('/notifications/{id}', [NotificationController::class, 'read'])->name('admin.notifications.read');

    // ---------------------------------------------------------------
    // HOA Center
    // ---------------------------------------------------------------
    Route::prefix('hoa-center')->name('hoa-center.')->group(function () {
        Route::get('/members', [HOACenterController::class, 'members'])->name('members');
        Route::get('/announcements', [HOACenterController::class, 'announcements'])->name('announcements');
        Route::get('/directory', [HOACenterController::class, 'directory'])->name('directory');
        Route::get('/faqs', [HOACenterController::class, 'faqs'])->name('faqs');
        Route::get('/contents', [HOACenterController::class, 'contents'])->name('contents');
    });

    // ---------------------------------------------------------------
    // Homeowners
    // ---------------------------------------------------------------
    Route::prefix('homeowners')->name('homeowners.')->group(function () {
        Route::get('/addresses', [HomeownerController::class, 'addresses'])->name('addresses');
        Route::get('/properties', [HomeownerController::class, 'properties'])->name('properties');
        Route::get('/households', [HomeownerController::class, 'households'])->name('households');
        Route::get('/residents', [HomeownerController::class, 'residents'])->name('residents');
        Route::get('/residents/{resident}/valid-id', [ResidentValidIdController::class, 'show'])
            ->name('residents.valid-id');
        Route::get('/pending-registrations', [HomeownerController::class, 'pendingRegistrations'])
            ->name('pending-registrations');
        Route::get('/', [HomeownerController::class, 'homeowners'])->name('homeowners');
    });

    // ---------------------------------------------------------------
    // Amenities Booking
    // ---------------------------------------------------------------
    Route::prefix('amenities')->name('amenities.')->group(function () {
        Route::get('/bookings', [AmenityController::class, 'bookings'])->name('bookings');
        Route::get('/calendar', [AmenityController::class, 'calendar'])->name('calendar');
        Route::get('/', [AmenityController::class, 'index'])->name('index');
    });

    // ---------------------------------------------------------------
    // Collections
    // ---------------------------------------------------------------
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/invoices', [CollectionController::class, 'invoices'])->name('invoices');
        Route::get('/monthly-dues-invoices', [CollectionController::class, 'monthlyDueInvoices'])->name('monthly-dues-invoices');
        Route::get('/monthly-dues-table', [CollectionController::class, 'monthlyDueTable'])->name('monthly-dues-table');
        Route::get('/monthly-dues-generator', [CollectionController::class, 'monthlyDuesGenerator'])->name('monthly-dues-generator');
        Route::get('/payments', [CollectionController::class, 'payments'])->name('payments');
        Route::get('/payments/{payment}/receipt', [ReceiptController::class, 'show'])->name('payments.receipt.show');
        Route::get('/payments/{payment}/receipt/download', [ReceiptController::class, 'download'])->name('payments.receipt.download');
        Route::get('/payments/{payment}/receipt/thermal', [ReceiptController::class, 'showThermal'])->name('payments.receipt.thermal.show');
        Route::get('/payments/{payment}/receipt/thermal/download', [ReceiptController::class, 'downloadThermal'])->name('payments.receipt.thermal.download');
        Route::get('/advance-deposits', [CollectionController::class, 'advanceDeposits'])->name('advance-deposits');
        Route::get('/products', [CollectionController::class, 'products'])->name('products');
        Route::get('/product-categories', [CollectionController::class, 'productCategories'])->name('product-categories');
        Route::get('/soa', [CollectionController::class, 'soa'])->name('soa');
    });

    // ---------------------------------------------------------------
    // Expenses
    // ---------------------------------------------------------------
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories');
        Route::get('/suppliers', [ExpenseController::class, 'suppliers'])->name('suppliers');
    });

    // ---------------------------------------------------------------
    // Accounting
    // ---------------------------------------------------------------
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/chart-of-accounts', [AccountingController::class, 'chartOfAccounts'])->name('chart-of-accounts');
        Route::get('/journal-entries', [AccountingController::class, 'journalEntries'])->name('journal-entries');
        Route::get('/journal-entries/{id}/voucher', [AccountingController::class, 'voucher'])->name('journal-entries.voucher');
        Route::get('/transfers', [AccountingController::class, 'transfers'])->name('transfers');
        Route::get('/cash-funding', [AccountingController::class, 'cashFunding'])->name('cash-funding');
        Route::get('/transactions', [AccountingController::class, 'transactions'])->name('transactions');
        Route::get('/period-close', [AccountingController::class, 'periodClose'])->name('period-close');
    });

    // ---------------------------------------------------------------
    // Support
    // ---------------------------------------------------------------
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/requests', [SupportController::class, 'requests'])->name('requests');
        Route::get('/violations', [SupportController::class, 'violations'])->name('violations');
        Route::get('/violations/{id}/notice', [SupportController::class, 'violationNotice'])->name('violations.notice');
        Route::get('/complaints', [SupportController::class, 'complaints'])->name('complaints');
    });

    // ---------------------------------------------------------------
    // RBI Forms
    // ---------------------------------------------------------------
    Route::prefix('rbi')->name('rbi.')->group(function () {
        Route::get('/form-a', [RbiFormController::class, 'formA'])->name('form-a');
        Route::get('/form-a/{household}/sheet', [RbiFormController::class, 'formASheet'])->name('form-a.sheet');
        Route::get('/form-a/{household}/download', [RbiFormController::class, 'formADownload'])->name('form-a.download');
        Route::get('/form-b', [RbiFormController::class, 'formB'])->name('form-b');
        Route::get('/form-b/{resident}/sheet', [RbiFormController::class, 'formBSheet'])->name('form-b.sheet');
        Route::get('/form-b/{resident}/download', [RbiFormController::class, 'formBDownload'])->name('form-b.download');
        Route::get('/form-c', [RbiFormController::class, 'formC'])->name('form-c');
        Route::get('/form-c/sheet', [RbiFormController::class, 'formCSheet'])->name('form-c.sheet');
        Route::get('/form-c/download', [RbiFormController::class, 'formCDownload'])->name('form-c.download');
    });

    // ---------------------------------------------------------------
    // Print Documents
    // ---------------------------------------------------------------
    Route::prefix('print')->name('print.')->group(function () {
        Route::get('/{document}', [PrintDocumentController::class, 'show'])->name('show');
        Route::get('/{document}/pdf', [PrintDocumentController::class, 'pdf'])->name('pdf');
    });

    // ---------------------------------------------------------------
    // Reports
    // ---------------------------------------------------------------
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/ar-aging', [ReportController::class, 'arAging'])->name('ar-aging');
        Route::get('/monthly-income', [ReportController::class, 'monthlyIncome'])->name('monthly-income');
        Route::get('/monthly-expenses', [ReportController::class, 'monthlyExpenses'])->name('monthly-expenses');
        Route::get('/daily-collection', [ReportController::class, 'dailyCollection'])->name('daily-collection');
        Route::get('/daily-income', [ReportController::class, 'dailyIncome'])->name('daily-income');
        Route::get('/daily-expenses', [ReportController::class, 'dailyExpenses'])->name('daily-expenses');
        Route::get('/profit-and-loss', [ReportController::class, 'profitAndLoss'])->name('profit-and-loss');
        Route::get('/statement-of-account', [ReportController::class, 'statementOfAccount'])->name('statement-of-account');
        Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/community', [ReportController::class, 'community'])->name('community');
        Route::get('/residents', [ReportController::class, 'residents'])->name('residents');
        Route::get('/amenities', [ReportController::class, 'amenities'])->name('amenities');
    });

    // ---------------------------------------------------------------
    // User Management (Administrator only)
    // ---------------------------------------------------------------
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/roles', [UserManagementController::class, 'roles'])->name('roles');
    });

    // ---------------------------------------------------------------
    // Settings (Administrator only)
    // ---------------------------------------------------------------
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'general'])->name('general');
        Route::get('/print', [SettingsController::class, 'print'])->name('print');
        Route::get('/collections', [SettingsController::class, 'collections'])->name('collections');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::get('/authentication', [SettingsController::class, 'authentication'])->name('authentication');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
    });


});

