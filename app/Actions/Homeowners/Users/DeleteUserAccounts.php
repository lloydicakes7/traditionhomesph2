<?php

namespace App\Actions\Homeowners\Users;

use App\Models\Admin;
use App\Models\User;
use App\Services\ActivityLogService;

class DeleteUserAccounts
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle($ids, Admin $by): int
    {
        $ids = (array) $ids;
        $count = count($ids);

        User::whereIn('id', $ids)->delete();

        $this->activity->log(
            $by,
            'deleted',
            'user',
            $count === 1
                ? "Deleted user account ID {$ids[0]}"
                : "Deleted {$count} user accounts"
        );

        return $count;
    }
}
