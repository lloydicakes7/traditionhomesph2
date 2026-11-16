<?php

namespace App\Enums;

/**
 * Where a resident's own profile stands before the portal opens to them.
 *
 * A self-registered account starts `incomplete`: it can sign in, but everything
 * except the onboarding form is closed. Saving that form asks the office to
 * check the person, the unit and the household, which is `submitted`; the office
 * either `verified` the record and the services open, or `rejected` it with a
 * note and the resident corrects it.
 *
 * @method static ProfileStatus INCOMPLETE()
 * @method static ProfileStatus SUBMITTED()
 * @method static ProfileStatus VERIFIED()
 * @method static ProfileStatus REJECTED()
 */
class ProfileStatus extends Enum
{
    const INCOMPLETE = 'incomplete';
    const SUBMITTED  = 'submitted';
    const VERIFIED   = 'verified';
    const REJECTED   = 'rejected';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::INCOMPLETE => 'Profile incomplete',
            self::SUBMITTED  => 'Waiting for the office',
            self::VERIFIED   => 'Verified',
            self::REJECTED   => 'Needs correction',
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
            self::INCOMPLETE => 'secondary',
            self::SUBMITTED  => 'warning',
            self::VERIFIED   => 'success',
            self::REJECTED   => 'danger',
        ][$this->value] ?? 'secondary';
    }

    /**
     * Whether the office has checked the profile, which opens the services.
     */
    public function isVerified(): bool
    {
        return $this->value === self::VERIFIED;
    }

    /**
     * Whether the profile is with the office right now.
     */
    public function isSubmitted(): bool
    {
        return $this->value === self::SUBMITTED;
    }

    /**
     * Whether the office asked for a correction.
     */
    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }
}
