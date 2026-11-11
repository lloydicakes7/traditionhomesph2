<?php

namespace App\Actions\HoaCenter\Announcements;

use App\Actions\HoaCenter\Announcements\Concerns\AnnouncementPayload;
use App\Models\Admin;
use App\Models\Announcement;
use App\Services\ActivityLogService;

class DeleteAnnouncement
{
    use AnnouncementPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Announcement $announcement, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'announcement',
            "Deleted announcement \"{$announcement->title}\"",
            ['announcement_id' => $announcement->id]
        );

        $this->deleteImage($announcement->image_path);

        $announcement->delete();
    }
}
