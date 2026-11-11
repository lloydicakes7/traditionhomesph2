<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;

class UpdateUserAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, array $data, Admin $by): User
    {
        $user = User::findOrFail($id);

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (array_key_exists('is_active', $data)) {
            $user->is_active = (bool) $data['is_active'];
        }

        $user->save();

        $this->activity->log(
            $by,
            'updated',
            'user',
            "Updated user account for {$user->name} ({$user->email})",
            ['user_id' => $user->id]
        );

        return $user->fresh()->load('resident');
    }
}
