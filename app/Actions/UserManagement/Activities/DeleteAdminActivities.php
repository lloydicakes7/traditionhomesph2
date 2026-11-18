<?php

namespace App\Actions\UserManagement\Activities;

use App\Models\AdminActivity;

class DeleteAdminActivities
{
    /**
     * Delete the given activity logs and return how many were removed.
     */
    public function handle(array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return 0;
        }

        return AdminActivity::whereIn('id', $ids)->delete();
    }

    /**
     * Delete every activity log recorded by one admin and return the count.
     */
    public function forAdmin(int $adminId): int
    {
        return AdminActivity::where('admin_id', $adminId)->delete();
    }
}
