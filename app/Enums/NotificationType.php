<?php

namespace App\Enums;

/**
 * The kinds of thing the app tells an account about.
 *
 * A notification's row stores its kind and nothing more about how it looks:
 * the icon beside the line and the colour it reads in are decided here, so a
 * new kind is one case in this file and every panel that lists notifications
 * shows it the same way (see App\Notifications\ThhaNotification and the
 * notification partials).
 *
 * The kind is deliberately coarse — a booking is a booking, whether it was
 * filed, approved or withdrawn. What happened is the line's own words; this
 * only says which part of the app it belongs to.
 *
 * @method static NotificationType WELCOME()
 * @method static NotificationType BOOKING()
 * @method static NotificationType REQUEST()
 * @method static NotificationType COMPLAINT()
 * @method static NotificationType VIOLATION()
 * @method static NotificationType ANNOUNCEMENT()
 * @method static NotificationType STATEMENT()
 * @method static NotificationType PAYMENT()
 * @method static NotificationType OFFICER()
 * @method static NotificationType HOMEOWNER()
 */
class NotificationType extends Enum
{
    const WELCOME      = 'welcome';
    const BOOKING      = 'booking';
    const REQUEST      = 'request';
    const COMPLAINT    = 'complaint';
    const VIOLATION    = 'violation';
    const ANNOUNCEMENT = 'announcement';
    const STATEMENT    = 'statement';
    const PAYMENT      = 'payment';
    const OFFICER      = 'officer';
    const HOMEOWNER    = 'homeowner';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::WELCOME      => 'Welcome',
            self::BOOKING      => 'Booking',
            self::REQUEST      => 'Request',
            self::COMPLAINT    => 'Complaint',
            self::VIOLATION    => 'Violation',
            self::ANNOUNCEMENT => 'Announcement',
            self::STATEMENT    => 'Statement',
            self::PAYMENT      => 'Payment',
            self::OFFICER      => 'User Management',
            self::HOMEOWNER    => 'Homeowners',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The Font Awesome icon this kind reads with.
     */
    public function icon(): string
    {
        return [
            self::WELCOME      => 'fas fa-fw fa-hand-sparkles',
            self::BOOKING      => 'far fa-fw fa-calendar-check',
            self::REQUEST      => 'far fa-fw fa-clipboard',
            self::COMPLAINT    => 'fas fa-fw fa-question-circle',
            self::VIOLATION    => 'fas fa-fw fa-exclamation-triangle',
            self::ANNOUNCEMENT => 'fas fa-fw fa-bullhorn',
            self::STATEMENT    => 'fas fa-fw fa-file-invoice-dollar',
            self::PAYMENT      => 'fas fa-fw fa-money-check-alt',
            self::OFFICER      => 'fas fa-fw fa-users-cog',
            self::HOMEOWNER    => 'fas fa-fw fa-user-plus',
        ][$this->value] ?? 'fas fa-fw fa-bell';
    }

    /**
     * The AdminLTE badge theme this kind reads in — decided here, never by a
     * view's own conditional.
     */
    public function badgeTheme(): string
    {
        return [
            self::WELCOME      => 'primary',
            self::BOOKING      => 'purple',
            self::REQUEST      => 'orange',
            self::COMPLAINT    => 'warning',
            self::VIOLATION    => 'danger',
            self::ANNOUNCEMENT => 'maroon',
            self::STATEMENT    => 'navy',
            self::PAYMENT      => 'teal',
            self::OFFICER      => 'info',
            self::HOMEOWNER    => 'primary',
        ][$this->value] ?? 'secondary';
    }
}
