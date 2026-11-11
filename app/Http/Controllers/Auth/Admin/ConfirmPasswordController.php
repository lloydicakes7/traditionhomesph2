<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Auth\ConfirmPasswordController as Controller;
use App\Providers\RouteServiceProvider;

/**
 * The password confirmation an officer sees before a protected admin action.
 */
class ConfirmPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function guardName(): string
    {
        return 'web';
    }

    protected function home(): string
    {
        return RouteServiceProvider::ADMIN_HOME;
    }

    protected function formView(): string
    {
        return 'auth.admin.confirm-password';
    }
}
