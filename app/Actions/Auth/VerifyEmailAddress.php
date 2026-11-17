<?php

namespace App\Actions\Auth;

use App\Models\User;

/**
 * Mark an account's email address as confirmed.
 */
class VerifyEmailAddress
{
    public function handle(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->markEmailAsVerified();
    }
}
