<?php

namespace App\Enums;

/**
 * Where a violation issued against a homeowner stands.
 *
 * A violation is raised ACTIVE and stays that way — on the member's current
 * record — until the office resolves it. Resolving does not remove the record:
 * it moves it out of the member's active list and into their violation history,
 * which is why this enum owns the question "does this still count against the
 * homeowner?" rather than each page asking it with its own status list.
 *
 * Acknowledging is deliberately not a status. A homeowner acknowledging a
 * violation means they received and read the notice, never that they accept it,
 * so the acknowledgment is stamped beside the status (acknowledged_at) instead
 * of replacing it.
 *
 * @method static ViolationStatus ACTIVE()
 * @method static ViolationStatus RESOLVED()
 */
class ViolationStatus extends Enum
{
    const ACTIVE   = 'active';
    const RESOLVED = 'resolved';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE   => 'Unresolved',
            self::RESOLVED => 'Resolved',
        ];
    }

    /**
     * Map of value → what the status means, for the member's own reading.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::ACTIVE   => 'Unresolved — the association has not closed this violation yet.',
            self::RESOLVED => 'Resolved — the office has closed this violation.',
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
            self::ACTIVE   => 'danger',
            self::RESOLVED => 'success',
        ][$this->value] ?? 'secondary';
    }

    /**
     * AdminLTE badge theme the status reads in beside the member's own record —
     * a resolved one is history, so it reads quieter than the current list.
     */
    public function historyBadgeTheme(): string
    {
        return self::RESOLVED === $this->value ? 'secondary' : $this->badgeTheme();
    }

    /**
     * Whether this violation still stands against the homeowner — it is on
     * their current list rather than in their history.
     */
    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    /**
     * Whether this violation has been closed by the office.
     */
    public function isResolved(): bool
    {
        return $this->value === self::RESOLVED;
    }

    /**
     * Whether the office can still resolve this violation.
     */
    public function canBeResolved(): bool
    {
        return $this->isActive();
    }

    /**
     * Whether nothing else happens to this violation.
     */
    public function isFinal(): bool
    {
        return $this->isResolved();
    }
}
