<?php

namespace App\Enums;

/**
 * Whether a billable item is a thing or a service.
 *
 * The distinction is what the office sells (a sticker, an ID) as opposed to
 * what it performs (a clearance, a garbage collection). It only labels the
 * item — how it is billed is its price — so the enum carries no logic beyond
 * how it reads.
 *
 * @method static ProductType PRODUCT()
 * @method static ProductType SERVICE()
 */
class ProductType extends Enum
{
    const PRODUCT = 'product';
    const SERVICE = 'service';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PRODUCT => 'Product',
            self::SERVICE => 'Service',
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
     * AdminLTE badge theme class for this type.
     */
    public function badgeTheme(): string
    {
        return $this->value === self::PRODUCT ? 'primary' : 'info';
    }
}
