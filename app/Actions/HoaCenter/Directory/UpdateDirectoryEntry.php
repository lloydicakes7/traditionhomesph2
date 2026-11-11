<?php

namespace App\Actions\HoaCenter\Directory;

use App\Actions\HoaCenter\Directory\Concerns\DirectoryEntryPayload;
use App\Models\Admin;
use App\Models\Directory\DirectoryEntry;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateDirectoryEntry
{
    use DirectoryEntryPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(DirectoryEntry $entry, array $data, Admin $by): DirectoryEntry
    {
        DB::transaction(function () use ($entry, $data) {
            $group = $this->requireGroup(
                $data['directory_group_id'] ?? null,
                $data['new_group_name'] ?? null
            );

            $entry->update($this->payload($data, $group));

            $entry->phones()->delete();
            $entry->phones()->createMany($this->phoneRows($data['phones'] ?? []));
        });

        $this->activity->log(
            $by,
            'updated',
            'directory',
            "Updated directory entry {$entry->name}",
            ['directory_entry_id' => $entry->id]
        );

        return $entry->fresh(['group', 'phones']);
    }
}
