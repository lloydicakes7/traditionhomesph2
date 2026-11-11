<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\Portal\ConfirmPasswordController;
use App\Http\Controllers\Portal\HomeController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\PersonalInformationController;
use App\Http\Controllers\Portal\HouseholdPropertyController;
use App\Http\Controllers\Portal\AnnouncementController;
use App\Http\Controllers\Portal\BookingController;
use App\Http\Controllers\Portal\MemberController;
use App\Http\Controllers\Portal\RequestController;
use App\Http\Controllers\Portal\ComplaintController;
use App\Http\Controllers\Portal\ViolationController;
use App\Http\Controllers\Portal\StatementOfAccountController;
use App\Http\Controllers\Portal\PaymentController;
use App\Http\Controllers\Portal\Amenities\BookingSlipController;
use App\Http\Controllers\Portal\Support\RequestSlipController;
use App\Http\Controllers\Portal\Rbi\RbiFormController;
use App\Http\Controllers\Portal\NotificationController;
use App\Http\Controllers\Portal\ManualController;

/*
|--------------------------------------------------------------------------
| Homeowner Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the homeowner portal. They are loaded by the
| RouteServiceProvider within the "web" middleware group and prefixed
| with "portal".
*/

Route::middleware(['guest:web', 'guest:homeowner'])->group(function () {

    // ---------------------------------------------------------------
    // Registration
    // ---------------------------------------------------------------

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
        ->middleware('registration.open:resident')
        ->name('register');
});

Route::middleware([
    'auth:homeowner',
    'account.active:homeowner',
    'password.expiry:homeowner,portal.profile',
])->group(function () {

    // ---------------------------------------------------------------
    // Authentication
    // ---------------------------------------------------------------

    // Logout
    Route::post('logout', LogoutController::class)->name('portal.logout');

    // Password Confirm
    Route::get('password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('portal.password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'confirm']);

    // ---------------------------------------------------------------
    // Home
    // ---------------------------------------------------------------

    Route::get('/', [HomeController::class, 'index'])->name('portal.dashboard');

    // ---------------------------------------------------------------
    // User Manual
    // ---------------------------------------------------------------

    Route::get('manual/{topic?}', [ManualController::class, 'index'])
        ->middleware('manual.locale')->name('portal.manual');

    // ---------------------------------------------------------------
    // Notifications
    // ---------------------------------------------------------------

    Route::get('notifications', [NotificationController::class, 'index'])->name('portal.notifications');
    Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('portal.notifications.feed');
    Route::get('notifications/{id}', [NotificationController::class, 'read'])->name('portal.notifications.read');

    // ---------------------------------------------------------------
    // Services
    // ---------------------------------------------------------------

    Route::get('bookings', [BookingController::class, 'index'])->name('portal.bookings');
    Route::get('bookings/{id}/slip', [BookingSlipController::class, 'download'])->name('portal.bookings.slip');
    Route::get('requests', [RequestController::class, 'index'])->name('portal.requests');
    Route::get('requests/{id}/slip', [RequestSlipController::class, 'download'])->name('portal.requests.slip');
    Route::get('complaints', [ComplaintController::class, 'index'])->name('portal.complaints');
    Route::get('violations', [ViolationController::class, 'index'])->name('portal.violations');

    // ---------------------------------------------------------------
    // Finance
    // ---------------------------------------------------------------

    Route::get('statement-of-account', [StatementOfAccountController::class, 'index'])->name('portal.statement-of-account');
    Route::get('payments', [PaymentController::class, 'index'])->name('portal.payments');

    // ---------------------------------------------------------------
    // My Account
    // ---------------------------------------------------------------

    Route::get('account-details', [ProfileController::class, 'index'])->name('portal.profile');

    // ---------------------------------------------------------------
    // RBI Forms
    // ---------------------------------------------------------------

    Route::prefix('rbi')->name('portal.rbi.')->group(function () {
        Route::get('/', [RbiFormController::class, 'index'])->name('index');
        Route::get('form-a/{household}', [RbiFormController::class, 'formA'])->name('form-a');
        Route::get('form-a/{household}/sheet', [RbiFormController::class, 'formASheet'])->name('form-a.sheet');
        Route::get('form-a/{household}/download', [RbiFormController::class, 'formADownload'])->name('form-a.download');
        Route::get('form-b/{resident}', [RbiFormController::class, 'formB'])->name('form-b');
        Route::get('form-b/{resident}/sheet', [RbiFormController::class, 'formBSheet'])->name('form-b.sheet');
        Route::get('form-b/{resident}/download', [RbiFormController::class, 'formBDownload'])->name('form-b.download');
    });
    Route::get('personal-information', [PersonalInformationController::class, 'index'])->name('portal.personal-information');
    Route::get('household-property', [HouseholdPropertyController::class, 'index'])->name('portal.household-property');

});
