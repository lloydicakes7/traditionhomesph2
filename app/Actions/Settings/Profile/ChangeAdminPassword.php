<?php

namespace App\Actions\Settings\Profile;

use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Hash;

class ChangeAdminPassword
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $admin->password)) {
            return false;
        }

        $admin->password = Hash::make($newPassword);
        $admin->password_changed_at = now();
        $admin->save();

        $this->activity->log($admin, 'updated', 'profile', 'Changed password', ['user_id' => $admin->id]);

        return true;
    }
}
