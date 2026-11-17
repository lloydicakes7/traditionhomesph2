<?php

namespace App\Actions\Auth;

use App\Models\User;

/**
 * Send a fresh verification link to an account that still needs one.
 *
 * The answer says the same thing whether or not the address is on file, so the
 * form cannot be used to learn which addresses have accounts.
 */
class ResendEmailVerification
{
    public function handle(string $email): void
    {
        $user = User::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}
