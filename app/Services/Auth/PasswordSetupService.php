<?php

namespace App\Services\Auth;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\OfficerAccountOpened;
use App\Notifications\SetPasswordNotification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

/**
 * Issues the one link that lets an account choose its own password.
 *
 * A homeowner receives a signed link to the set-password screen, which expires
 * with the `users` broker's own token. An officer receives one of the `admins`
 * broker's reset links, which the account opens on the reset screen: a welcome
 * when the account has just been opened, a reset afterwards. Either way the
 * link is a secret the office never sees or chooses a password from.
 */
class PasswordSetupService
{
    // ===== Operations =====

    public function send(Admin|User $account): void
    {
        if ($account instanceof Admin) {
            Password::broker('admins')->sendResetLink(['email' => $account->email]);

            return;
        }

        $minutes = (int) config('auth.passwords.users.expire', 60);

        $url = URL::temporarySignedRoute(
            'password.set',
            now()->addMinutes($minutes),
            [
                'token' => Password::broker('users')->createToken($account),
                'email' => $account->email,
            ]
        );

        $account->notify(new SetPasswordNotification($url));
    }

    /**
     * Welcome a newly opened officer account: the link is the reset screen's,
     * but the mail says the account is ready instead of reporting a request.
     */
    public function sendWelcome(Admin $admin): void
    {
        $url = URL::route('password.reset', [
            'token' => Password::broker('admins')->createToken($admin),
            'email' => $admin->email,
        ]);

        $admin->notify(new OfficerAccountOpened($url));
    }
}
