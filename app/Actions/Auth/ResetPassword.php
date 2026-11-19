<?php

namespace App\Actions\Auth;

use App\Models\Admin;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Set a new password from an emailed reset link, for either account type.
 *
 * The address decides which broker verifies the token. The account is never
 * signed in here: it signs in again at `/login` with the new password. A link
 * for an address with no account fails on the same "invalid token" answer as a
 * tampered one.
 */
class ResetPassword
{
    // ===== Handle =====

    /**
     * @param  array<string, string>  $data  token, email, password, password_confirmation
     * @return string  The broker's status, `passwords.reset` when it worked
     */
    public function handle(array $data): string
    {
        $email = strtolower(trim((string) $data['email']));
        $isAdmin = Admin::where('email', $email)->exists();

        $status = Password::broker($isAdmin ? 'admins' : 'users')->reset(
            [
                'email'                 => $email,
                'password'              => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token'                 => $data['token'],
            ],
            function (CanResetPassword $account, string $password): void {
                $this->store($account, $password);
            }
        );

        return $status;
    }

    // ===== Tools =====

    /**
     * Write the new password, and stamp it as the newest one.
     */
    private function store(CanResetPassword $account, string $password): void
    {
        $account->forceFill([
            'password'            => Hash::make($password),
        ])->save();
    }
}
