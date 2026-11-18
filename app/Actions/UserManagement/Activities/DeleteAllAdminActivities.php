<?php

namespace App\Actions\UserManagement\Activities;

use App\Models\AdminActivity;

class DeleteAllAdminActivities
{
    public function handle(): int
    {
        return AdminActivity::query()->delete();
    }
}
