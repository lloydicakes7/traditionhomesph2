<?php

namespace App\Actions\HoaCenter\Directory;

use App\Actions\HoaCenter\Directory\Concerns\DirectoryEntryPayload;
use App\Models\Admin;
use App\Models\Directory\DirectoryEntry;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateDirectoryEntry
{
    use DirectoryEntryPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): DirectoryEntry
    {
        $entry = DB::transaction(function () use ($data) {
            $group = $this->requireGroup(
                $data['directory_group_id'] ?? null,
                $data['new_group_name'] ?? null
            );

            $entry = DirectoryEntry::create($this->payload($data, $group));

            $entry->phones()->createMany($this->phoneRows($data['phones'] ?? []));

            return $entry;
        });

        $this->activity->log(
            $by,
            'created',
            'directory',
            "Created directory entry {$entry->name} in {$entry->group->name}",
            ['directory_entry_id' => $entry->id]
        );

        return $entry->fresh(['group', 'phones']);
    }
}
