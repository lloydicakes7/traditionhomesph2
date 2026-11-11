<?php

namespace App\Enums;

/**
 * Where a complaint stands, from the moment it is filed to the day it is closed.
 *
 * A complaint is filed by a homeowner from the portal or by an officer for
 * something reported to the office, and both kinds move through the same four
 * states the office works in: waiting to be picked up, being looked into,
 * addressed, and closed. The enum is the one place that says which of those the
 * office can still move a complaint out of, so no page re-derives it from a list
 * of statuses.
 *
 * @method static ComplaintStatus PENDING()
 * @method static ComplaintStatus IN_PROGRESS()
 * @method static ComplaintStatus RESOLVED()
 * @method static ComplaintStatus CLOSED()
 */
class ComplaintStatus extends Enum
{
    const PENDING     = 'pending';
    const IN_PROGRESS = 'in_progress';
    const RESOLVED    = 'resolved';
    const CLOSED      = 'closed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING     => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::RESOLVED    => 'Resolved',
            self::CLOSED      => 'Closed',
        ];
    }

    /**
     * Map of value → what the status means, for the submitter's own reading.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::PENDING     => 'Submitted and waiting for the office to pick it up.',
            self::IN_PROGRESS => 'The office is looking into it.',
            self::RESOLVED    => 'Addressed by the office — see the response.',
            self::CLOSED      => 'Closed — no further action will be taken.',
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
     * What this status means, for this instance.
     */
    public function description(): string
    {
        return self::descriptions()[$this->value] ?? '';
    }

    /**
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::PENDING     => 'warning',
            self::IN_PROGRESS => 'info',
            self::RESOLVED    => 'success',
            self::CLOSED      => 'secondary',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the office still has something to do with this complaint.
     */
    public function isOpen(): bool
    {
        return in_array($this->value, [self::PENDING, self::IN_PROGRESS], true);
    }

    /**
     * Whether nothing else happens to this complaint.
     */
    public function isFinal(): bool
    {
        return in_array($this->value, [self::RESOLVED, self::CLOSED], true);
    }

    /**
     * Whether a response can still be written against this complaint — a
     * closed one is done with, and reopening it is not one of the moves the
     * office makes.
     */
    public function canBeRespondedTo(): bool
    {
        return $this->value !== self::CLOSED;
    }
}
