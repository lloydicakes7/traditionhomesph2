<?php

namespace App\Actions\Auth;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;

/**
 * Sign in whichever account holds the email address.
 *
 * One session carries one identity: the side signing in ends the other side's
 * session first, so a browser that still holds a portal session can open the
 * officer panel — and the other way round.
 */
class SignInAccount
{
    const ADMIN = 'admin';

    const HOMEOWNER = 'homeowner';

    const PENDING = 'pending';

    const FAILED = 'failed';

    /** @var ActivityLogService */
    private $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $email, string $password, bool $remember, string $ip): string
    {
        $email = strtolower(trim($email));

        $admin = $this->match(Auth::guard('web')->getProvider(), $email, $password, Admin::class);

        if ($admin instanceof Admin) {
            return $admin->is_active ? $this->signInAdmin($admin, $remember, $ip) : self::PENDING;
        }

        $user = $this->match(Auth::guard('homeowner')->getProvider(), $email, $password, User::class);

        if ($user instanceof User) {
            $maySignIn = $user->is_active && ! $user->isPendingRegistration();

            return $maySignIn ? $this->signInHomeowner($user, $remember) : self::PENDING;
        }

        return self::FAILED;
    }

    // ===== Sign in =====

    private function signInAdmin(Admin $admin, bool $remember, string $ip): string
    {
        Auth::guard('homeowner')->logout();

        Auth::guard('web')->login($admin, $remember);

        request()->session()->regenerate();

        $this->activity->log(
            $admin,
            'logged_in',
            'auth',
            "Logged in ({$admin->name}, {$admin->email})",
            ['admin_id' => $admin->id, 'ip' => $ip]
        );

        return self::ADMIN;
    }

    private function signInHomeowner(User $user, bool $remember): string
    {
        Auth::guard('web')->logout();

        Auth::guard('homeowner')->login($user, $remember);

        request()->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return self::HOMEOWNER;
    }

    // ===== Tools =====

    private function match(UserProvider $provider, string $email, string $password, string $model): ?object
    {
        $account = $provider->retrieveByCredentials(['email' => $email]);

        if (! $account instanceof $model) {
            return null;
        }

        return $provider->validateCredentials($account, ['password' => $password]) ? $account : null;
    }
}
