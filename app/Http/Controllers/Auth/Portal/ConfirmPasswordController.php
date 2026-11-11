<?php

namespace App\Http\Controllers\Auth\Portal;

use App\Http\Controllers\Auth\ConfirmPasswordController as Controller;
use App\Providers\RouteServiceProvider;

/**
 * The password confirmation a homeowner sees before a protected portal action.
 */
class ConfirmPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:homeowner');
    }

    protected function guardName(): string
    {
        return 'homeowner';
    }

    protected function home(): string
    {
        return RouteServiceProvider::HOMEOWNER_HOME;
    }

    protected function formView(): string
    {
        return 'auth.user.confirm-password';
    }
}
