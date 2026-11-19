<?php

namespace App\Enums;

/**
 * The name suffixes a resident record may carry.
 *
 * @method static Suffix JR()
 * @method static Suffix SR()
 * @method static Suffix I()
 * @method static Suffix II()
 * @method static Suffix III()
 * @method static Suffix IV()
 * @method static Suffix V()
 */
class Suffix extends Enum
{
    const JR  = 'Jr.';
    const SR  = 'Sr.';
    const I   = 'I';
    const II  = 'II';
    const III = 'III';
    const IV  = 'IV';
    const V   = 'V';

    /**
     * Map of value → human-readable label.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::JR  => 'Jr.',
            self::SR  => 'Sr.',
            self::I   => 'I',
            self::II  => 'II',
            self::III => 'III',
            self::IV  => 'IV',
            self::V   => 'V',
        ];
    }

    /**
     * Human-readable label for this instance.
     */
    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }
}
