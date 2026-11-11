<?php

namespace App\Actions\Auth;

use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

/**
 * Sign out whichever account holds the session.
 *
 * Both guards are cleared, because the two account types share one session and
 * one sign-out screen.
 */
class SignOutAccount
{
    /** @var ActivityLogService */
    private $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $ip): void
    {
        $admin = Auth::guard('web')->user();

        if ($admin instanceof Admin) {
            $this->activity->log(
                $admin,
                'logged_out',
                'auth',
                "Logged out ({$admin->name}, {$admin->email})",
                ['admin_id' => $admin->id, 'ip' => $ip]
            );
        }

        Auth::guard('web')->logout();
        Auth::guard('homeowner')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
