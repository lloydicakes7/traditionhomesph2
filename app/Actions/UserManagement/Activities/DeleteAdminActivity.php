<?php

namespace App\Actions\UserManagement\Activities;

use App\Models\AdminActivity;

class DeleteAdminActivity
{
    // ===== Handle =====

    public function handle(AdminActivity $activity): void
    {
        $activity->delete();
    }
}
