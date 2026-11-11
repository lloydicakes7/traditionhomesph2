<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Set the password a homeowner never had, from the signed link the office
 * mailed. The account is not signed in here; it signs in at `/login`.
 */
class SetPassword
{
    // ===== Handle =====

    /**
     * @param  array<string, string>  $data  token, email, password, password_confirmation
     */
    public function handle(array $data): bool
    {
        $status = Password::broker('users')->reset(
            [
                'email'                 => strtolower(trim((string) $data['email'])),
                'password'              => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token'                 => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password'            => Hash::make($password),
                    'password_changed_at' => now(),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET;
    }
}
