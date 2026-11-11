<?php

namespace App\Actions\UserManagement\Users;

use App\Actions\UserManagement\Users\Concerns\AdminPayload;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Services\Auth\PasswordSetupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Open an officer's account. The office chooses everything but the password:
 * the account is created with one nobody knows, and the officer is mailed a
 * welcome with the link that lets them choose their own.
 */
class CreateAdmin
{
    use AdminPayload;

    private ActivityLogService $activity;

    private PasswordSetupService $passwords;

    public function __construct(ActivityLogService $activity, PasswordSetupService $passwords)
    {
        $this->activity = $activity;
        $this->passwords = $passwords;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Admin
    {
        $admin = Admin::create($this->payload($data) + [
            'password'            => Hash::make(Str::random(60)),
            'password_changed_at' => now(),
        ]);

        $this->passwords->sendWelcome($admin);

        $this->activity->log(
            $by,
            'created',
            'user',
            "Created admin account {$admin->name} ({$admin->email})",
            ['admin_id' => $admin->id]
        );

        return $admin;
    }
}
