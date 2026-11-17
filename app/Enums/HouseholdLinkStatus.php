<?php

namespace App\Enums;

class HouseholdLinkStatus extends Enum
{
    const PENDING  = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';

    public static function labels(): array
    {
        return [
            self::PENDING  => 'Waiting for the household head',
            self::APPROVED => 'Approved by the household head',
            self::REJECTED => 'Rejected by the household head',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public function badgeTheme(): string
    {
        return [
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        ][$this->value] ?? 'secondary';
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }
}
