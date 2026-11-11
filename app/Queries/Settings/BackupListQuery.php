<?php

namespace App\Queries\Settings;

use App\Utility\DatabaseBackupHelper;

class BackupListQuery
{
    // ===== Queries =====

    public function all(): array
    {
        return DatabaseBackupHelper::all();
    }
}
