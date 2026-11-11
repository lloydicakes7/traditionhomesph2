<?php

namespace App\Actions\Settings\Backup;

use App\Models\Admin;
use App\Utility\DatabaseBackupHelper;

class RestoreDatabaseBackup
{
    // ===== Handle =====

    public function handle(string $filename, Admin $by): void
    {
        DatabaseBackupHelper::restore($filename);
    }
}
