<?php

namespace App\Actions\UserManagement\Users;

use App\Actions\UserManagement\Users\Concerns\AdminPayload;
use App\Models\Admin;
use App\Services\ActivityLogService;

class UpdateAdmin
{
    use AdminPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Admin $admin, array $data, Admin $by): Admin
    {
        $admin->update($this->payload($data));

        $this->activity->log(
            $by,
            'updated',
            'user',
            "Updated admin account {$admin->name} ({$admin->email})",
            ['admin_id' => $admin->id]
        );

        return $admin->fresh('role');
    }
}
