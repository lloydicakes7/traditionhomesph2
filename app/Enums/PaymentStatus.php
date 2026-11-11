<?php

namespace App\Enums;

/**
 * Where a receipt stands, from the day it is submitted to the day the office
 * records it.
 *
 * A payment filed from the homeowner portal is **pending**: the money is
 * claimed, the proof is on file, but the bill has not been settled by it yet.
 * The office records it (see \App\Actions\Collections\Payments\ApprovePayment)
 * and only then does the bill recompute from it. A receipt the office takes itself
 * is recorded at once — the column's default is what says so.
 *
 * @method static PaymentStatus PENDING()
 * @method static PaymentStatus RECORDED()
 */
class PaymentStatus extends Enum
{
    const PENDING  = 'pending';
    const RECORDED = 'recorded';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PENDING  => 'Pending',
            self::RECORDED => 'Recorded',
        ];
    }

    /**
     * Map of value → what the status means, for the record's own page.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::PENDING  => 'Submitted and waiting — the office records it once the money is verified.',
            self::RECORDED => 'Recorded by the office against the bill.',
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
            self::PENDING  => 'warning',
            self::RECORDED => 'success',
        ][$this->value] ?? 'secondary';
    }
}
