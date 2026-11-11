<?php

namespace App\Enums;

/**
 * Where a statement of account email stands.
 *
 * An SOA is queued when the office prepares a period's statements, sent when
 * the mail leaves the system, and failed when it could not be delivered — the
 * office retries those. The page reads the status to decide what it can offer
 * to do next.
 *
 * @method static SoaStatus PENDING()
 * @method static SoaStatus SENT()
 * @method static SoaStatus FAILED()
 */
class SoaStatus extends Enum
{
    const PENDING = 'pending';
    const SENT    = 'sent';
    const FAILED  = 'failed';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::SENT    => 'Sent',
            self::FAILED  => 'Failed',
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
     * AdminLTE badge theme class for this status.
     */
    public function badgeTheme(): string
    {
        return [
            self::PENDING => 'warning',
            self::SENT    => 'success',
            self::FAILED  => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the statement still has to go out.
     */
    public function canBeSent(): bool
    {
        return $this->value !== self::SENT;
    }
}
