<?php

namespace App\Actions\Settings\Backup;

use App\Models\Admin;
use App\Utility\DatabaseBackupHelper;

class DeleteDatabaseBackup
{
    // ===== Handle =====

    public function handle(string $filename, Admin $by): bool
    {
        return DatabaseBackupHelper::delete($filename);
    }
}
