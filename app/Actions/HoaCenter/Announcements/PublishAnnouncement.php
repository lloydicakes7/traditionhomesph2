<?php

namespace App\Actions\HoaCenter\Announcements;

use App\Actions\HoaCenter\Announcements\Concerns\AnnouncementPayload;
use App\Models\Admin;
use App\Models\Announcement;
use App\Notifications\AnnouncementPublishedNotification;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class PublishAnnouncement
{
    use AnnouncementPayload;

    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $image, Admin $by): Announcement
    {
        $payload = $this->payload($data);
        $payload['published_at'] = now();

        if ($image) {
            $payload['image_path'] = $this->storeImage($image);
        }

        $announcement = Announcement::create($payload);

        $this->activity->log(
            $by,
            'created',
            'announcement',
            "Published announcement \"{$announcement->title}\"",
            ['announcement_id' => $announcement->id]
        );

        $this->notifications->toHomeowners(new AnnouncementPublishedNotification($announcement));

        return $announcement;
    }
}
