<?php

namespace App\Enums;

/**
 * Where a self-registered portal account stands before it becomes a homeowner.
 *
 * An account the office creates is approved at once; an account filed through
 * the public registration wizard waits as `pending` until an officer accepts it.
 * The switch is separate from `is_active`, which only says whether the office has
 * enabled an approved account.
 *
 * @method static RegistrationStatus PENDING()
 * @method static RegistrationStatus APPROVED()
 */
class RegistrationStatus extends Enum
{
    const PENDING  = 'pending';
    const APPROVED = 'approved';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING  => 'Waiting for approval',
            self::APPROVED => 'Approved',
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
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
        ][$this->value] ?? 'secondary';
    }
}
