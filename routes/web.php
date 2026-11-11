<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PendingApprovalController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Public\AnnouncementController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MemberController;
use App\Http\Controllers\Public\ServicesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');

Route::get('/members', [MemberController::class, 'index'])->name('members');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');

Route::get('/services', [ServicesController::class, 'index'])->name('services');

Route::get('/content/{slug}', [ContentController::class, 'show'])->name('content.show');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', LogoutController::class)->name('logout');
Route::get('/pending-approval', [PendingApprovalController::class, 'show'])->name('registration.pending');

// Password Reset
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Password Set
Route::get('/password/set/{token}', [SetPasswordController::class, 'show'])->middleware('signed')->name('password.set');
Route::post('/password/set', [SetPasswordController::class, 'update'])->name('password.set.update');
