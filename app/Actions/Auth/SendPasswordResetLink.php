<?php

namespace App\Actions\Auth;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Password;

/**
 * Mail a reset link to whichever account holds the address.
 *
 * The address decides which broker sends the mail. Nothing is returned that
 * would tell the caller whether the address has an account — or which kind.
 */
class SendPasswordResetLink
{
    // ===== Handle =====

    public function handle(string $email): void
    {
        $email = strtolower(trim($email));

        if (Admin::where('email', $email)->exists()) {
            Password::broker('admins')->sendResetLink(['email' => $email]);

            return;
        }

        if (User::where('email', $email)->exists()) {
            Password::broker('users')->sendResetLink(['email' => $email]);
        }
    }
}
