<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Announcement;
use Illuminate\Support\Str;

/**
 * The association published an announcement.
 *
 * It goes to every homeowner with a portal account — one row each, which is
 * what makes the bell light up for a post rather than an email nobody opens.
 * Officers are not told: they are the ones who publish, and the post is on
 * their own Announcements page (see
 * App\Actions\HoaCenter\Announcements\PublishAnnouncement).
 */
class AnnouncementPublishedNotification extends ThhaNotification
{
    /** @var \App\Models\Announcement */
    private $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function type(): NotificationType
    {
        return NotificationType::ANNOUNCEMENT();
    }

    protected function title($notifiable): string
    {
        return $this->announcement->title;
    }

    protected function body($notifiable): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', (string) $this->announcement->body)), 140);
    }

    protected function url($notifiable): ?string
    {
        return route('portal.dashboard');
    }
}
