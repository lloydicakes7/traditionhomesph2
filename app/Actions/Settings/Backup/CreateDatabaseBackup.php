<?php

namespace App\Actions\Settings\Backup;

use App\Models\Admin;
use App\Utility\DatabaseBackupHelper;

class CreateDatabaseBackup
{
    // ===== Handle =====

    public function handle(Admin $by): string
    {
        return DatabaseBackupHelper::create();
    }
}
