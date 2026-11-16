<?php

namespace App\Actions\Homeowners\Profiles;

use App\Enums\ProfileStatus;
use App\Models\User;
use App\Notifications\PendingProfileVerifications;
use App\Notifications\PendingProfileVerificationsDigest;
use App\Services\NotificationService;
use App\Utility\SettingsHelper;
use Illuminate\Support\Carbon;

class NotifyPendingProfileVerifications
{
    const DIGEST_GROUP = 'profile_verification';

    const DIGEST_KEY = 'digest_sent_at';

    const DIGEST_INTERVAL_DAYS = 3;

    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function handle(): int
    {
        $pending = User::query()
            ->where('profile_status', ProfileStatus::SUBMITTED)
            ->count();

        if ($pending === 0) {
            return 0;
        }

        $this->notifications->toOfficers(new PendingProfileVerifications($pending));

        if (! $this->digestIsDue()) {
            return $pending;
        }

        $sent = $this->notifications->toOfficers(new PendingProfileVerificationsDigest($pending));

        if ($sent > 0) {
            SettingsHelper::set(self::DIGEST_KEY, now()->toDateTimeString(), self::DIGEST_GROUP);
        }

        return $pending;
    }

    private function digestIsDue(): bool
    {
        $sentAt = trim((string) SettingsHelper::get(self::DIGEST_KEY, self::DIGEST_GROUP));

        if ($sentAt === '') {
            return true;
        }

        return Carbon::parse($sentAt)->addDays(self::DIGEST_INTERVAL_DAYS)->isPast();
    }
}
