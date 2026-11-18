<?php

namespace App\Utility;

use Carbon\Carbon;

/**
 * How long a read notification is kept before it is deleted.
 *
 * Technical only: it knows the window and the moment a line falls out of it,
 * never what a notification says. The page writes the deadline and the
 * pruning job deletes on it, so both read the same number.
 */
class NotificationRetentionHelper
{
    /** @var int Days a notification stays after it was read. */
    const READ_DAYS = 7;

    /**
     * The moment a notification read at the given time is deleted.
     *
     * @param  \DateTimeInterface|null  $readAt
     */
    public static function expiresAt($readAt): ?Carbon
    {
        if (! $readAt) {
            return null;
        }

        return Carbon::instance($readAt)->addDays(self::READ_DAYS);
    }

    /**
     * The moment a notification must have been read before to be deleted now.
     */
    public static function cutoff(?Carbon $now = null): Carbon
    {
        return ($now ?: Carbon::now())->subDays(self::READ_DAYS);
    }
}
