<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Confirm that the account behind a guard knows its own password, and stamp
 * the session so the confirmation holds for the window Laravel allows.
 */
class ConfirmPassword
{
    // ===== Handle =====

    public function handle(string $guard, string $password): bool
    {
        $account = Auth::guard($guard)->user();

        if ($account === null || ! Hash::check($password, (string) $account->password)) {
            return false;
        }

        request()->session()->put('auth.password_confirmed_at', time());

        return true;
    }
}
