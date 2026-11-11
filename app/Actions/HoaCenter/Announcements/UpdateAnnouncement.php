<?php

namespace App\Actions\HoaCenter\Announcements;

use App\Actions\HoaCenter\Announcements\Concerns\AnnouncementPayload;
use App\Models\Admin;
use App\Models\Announcement;
use App\Services\ActivityLogService;

class UpdateAnnouncement
{
    use AnnouncementPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(
        Announcement $announcement,
        array $data,
        $image,
        bool $removeImage,
        Admin $by
    ): Announcement {
        $payload = $this->payload($data);

        if ($image) {
            $payload['image_path'] = $this->replaceImage($image, $announcement->image_path);
        } elseif ($removeImage) {
            $this->deleteImage($announcement->image_path);
            $payload['image_path'] = null;
        }

        $announcement->update($payload);

        $this->activity->log(
            $by,
            'updated',
            'announcement',
            "Updated announcement \"{$announcement->title}\"",
            ['announcement_id' => $announcement->id]
        );

        return $announcement;
    }
}
