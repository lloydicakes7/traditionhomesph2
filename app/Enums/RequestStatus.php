<?php

namespace App\Enums;

/**
 * Where a request stands, from the day the homeowner filed it.
 *
 * A request is filed PENDING and waits for the office to decide on it. The
 * office either APPROVES it — the transaction slip becomes available and the
 * document is issued — or CANCELS it, which must say why. An approved request
 * is COMPLETED once the document has been handed over and nothing is left to
 * do; a completed or cancelled request is closed and is never rewritten.
 *
 * The enum is the one place that says which of those moves a request can still
 * make, so no page re-derives it from a list of statuses. Cancelling is the one
 * move that must say why, which the service's rules enforce and the record
 * keeps.
 *
 * @method static RequestStatus PENDING()
 * @method static RequestStatus APPROVED()
 * @method static RequestStatus COMPLETED()
 * @method static RequestStatus CANCELLED()
 */
class RequestStatus extends Enum
{
    const PENDING   = 'pending';
    const APPROVED  = 'approved';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING   => 'Pending',
            self::APPROVED  => 'Approved',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
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
            self::PENDING   => 'Submitted and waiting for the office to approve or cancel it.',
            self::APPROVED  => 'Approved — download the transaction slip and bring it to the HOA office.',
            self::COMPLETED => 'Completed — the office has released the document.',
            self::CANCELLED => 'Cancelled — see the remarks for why.',
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
     * AdminLTE badge theme class for this status — decided here, never by a
     * view's own conditional.
     */
    public function badgeTheme(): string
    {
        return [
            self::PENDING   => 'warning',
            self::APPROVED  => 'success',
            self::COMPLETED => 'primary',
            self::CANCELLED => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * The statuses a request filed in this one may still move to. The map is
     * the whole transition table — a status missing from a request's own row
     * can never be reached, and no move goes backwards.
     *
     * @return array<int, string>
     */
    public function canMoveTo(): array
    {
        return [
            self::PENDING   => [self::APPROVED, self::CANCELLED],
            self::APPROVED  => [self::COMPLETED],
            self::COMPLETED => [],
            self::CANCELLED => [],
        ][$this->value] ?? [];
    }

    /**
     * Whether the office still has to decide on a request in this status.
     */
    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    /**
     * Whether the office can no longer act on a request in this status.
     */
    public function isFinal(): bool
    {
        return in_array($this->value, [self::COMPLETED, self::CANCELLED], true);
    }

    /**
     * Whether the request was approved — the state that unlocks its transaction
     * slip and the document it asked for.
     */
    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }
}
