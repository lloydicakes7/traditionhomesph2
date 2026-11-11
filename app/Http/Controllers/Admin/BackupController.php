<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Utility\DatabaseBackupHelper;

class BackupController extends Controller
{
    public function download($filename)
    {
        $this->authorize('download', Setting::class);

        return DatabaseBackupHelper::download($filename);
    }
}
