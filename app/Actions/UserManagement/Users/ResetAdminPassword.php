<?php

namespace App\Actions\UserManagement\Users;

use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Services\Auth\PasswordSetupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * "Reset Password" for an officer's account: the password on file becomes
 * unusable immediately and the officer is mailed the link that lets them
 * choose a new one. The resetting officer never sees it.
 */
class ResetAdminPassword
{
    private ActivityLogService $activity;

    private PasswordSetupService $passwords;

    public function __construct(ActivityLogService $activity, PasswordSetupService $passwords)
    {
        $this->activity = $activity;
        $this->passwords = $passwords;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): Admin
    {
        $admin = Admin::findOrFail($id);

        $admin->forceFill([
            'password'            => Hash::make(Str::random(60)),
            'password_changed_at' => now(),
        ])->save();

        $this->passwords->send($admin);

        $this->activity->log(
            $by,
            'reset_password',
            'user',
            "Reset the password for {$admin->name} ({$admin->email})",
            ['admin_id' => $admin->id]
        );

        return $admin;
    }
}
