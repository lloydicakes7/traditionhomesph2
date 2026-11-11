<?php

namespace App\Enums;

/**
 * @method static RbiSemester FIRST()
 * @method static RbiSemester SECOND()
 */
class RbiSemester extends Enum
{
    const FIRST  = 'first';
    const SECOND = 'second';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::FIRST  => 'First Semester',
            self::SECOND => 'Second Semester',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * The calendar months the semester covers.
     *
     * @return array<int, int>
     */
    public function months(): array
    {
        return $this->value === self::FIRST ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
    }
}
