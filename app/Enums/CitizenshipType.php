<?php

namespace App\Enums;

/**
 * @method static CitizenshipType FILIPINO()
 * @method static CitizenshipType DUAL()
 * @method static CitizenshipType FOREIGNER()
 */
class CitizenshipType extends Enum
{
    const FILIPINO  = 'filipino';
    const DUAL      = 'dual_citizenship';
    const FOREIGNER = 'foreigner';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::FILIPINO  => 'Filipino',
            self::DUAL      => 'Dual Citizenship',
            self::FOREIGNER => 'Foreigner',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The type a stored `residents.citizenship` value belongs to.
     */
    public static function fromCitizenship(?string $citizenship): ?self
    {
        $citizenship = strtolower(trim((string) $citizenship));

        if ($citizenship === '') {
            return null;
        }

        if (strpos($citizenship, 'dual') !== false) {
            return self::DUAL();
        }

        if (strpos($citizenship, 'filipino') !== false) {
            return self::FILIPINO();
        }

        return self::FOREIGNER();
    }
}
