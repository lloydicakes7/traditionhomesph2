<?php

namespace App\Actions\HoaCenter\Directory;

use App\Models\Admin;
use App\Models\Directory\DirectoryEntry;
use App\Services\ActivityLogService;

class DeleteDirectoryEntry
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(DirectoryEntry $entry, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'directory',
            "Deleted directory entry {$entry->name}",
            ['directory_entry_id' => $entry->id]
        );

        $entry->delete();
    }
}
