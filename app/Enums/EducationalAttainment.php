<?php

namespace App\Enums;

/**
 * @method static EducationalAttainment NO_FORMAL()
 * @method static EducationalAttainment ELEMENTARY_LEVEL()
 * @method static EducationalAttainment ELEMENTARY_GRADUATE()
 * @method static EducationalAttainment HIGH_SCHOOL_LEVEL()
 * @method static EducationalAttainment HIGH_SCHOOL_GRADUATE()
 * @method static EducationalAttainment VOCATIONAL()
 * @method static EducationalAttainment COLLEGE_UNDERGRADUATE()
 * @method static EducationalAttainment COLLEGE_GRADUATE()
 * @method static EducationalAttainment POST_GRADUATE()
 * @method static EducationalAttainment DOCTORATE()
 */
class EducationalAttainment extends Enum
{
    const NO_FORMAL          = 'no_formal';
    const ELEMENTARY_LEVEL   = 'elementary_level';
    const ELEMENTARY_GRADUATE = 'elementary_graduate';
    const HIGH_SCHOOL_LEVEL  = 'high_school_level';
    const HIGH_SCHOOL_GRADUATE = 'high_school_graduate';
    const VOCATIONAL          = 'vocational';
    const COLLEGE_LEVEL       = 'college_undergraduate';
    const COLLEGE_GRADUATE    = 'college_graduate';
    const POST_GRADUATE       = 'post_graduate';
    const DOCTORATE           = 'doctorate';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::NO_FORMAL          => 'No Formal Education',
            self::ELEMENTARY_LEVEL   => 'Elementary Level',
            self::ELEMENTARY_GRADUATE => 'Elementary Graduate',
            self::HIGH_SCHOOL_LEVEL  => 'High School Level',
            self::HIGH_SCHOOL_GRADUATE => 'High School Graduate',
            self::VOCATIONAL          => 'Vocational Course',
            self::COLLEGE_LEVEL       => 'College UnderGraduate',
            self::COLLEGE_GRADUATE    => 'College Graduate',
            self::POST_GRADUATE       => 'Post Graduate',
            self::DOCTORATE           => 'Doctorate Degree',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    /**
     * How far the attainment goes, from no formal education to a doctorate.
     */
    public function rank(): int
    {
        return self::rankOf($this->value);
    }

    /**
     * The place a stored attainment value holds in the ladder, or zero when unknown.
     */
    public static function rankOf(string $value): int
    {
        $ladder = [
            self::NO_FORMAL,
            self::ELEMENTARY_LEVEL,
            self::ELEMENTARY_GRADUATE,
            self::HIGH_SCHOOL_LEVEL,
            self::HIGH_SCHOOL_GRADUATE,
            self::VOCATIONAL,
            self::COLLEGE_LEVEL,
            self::COLLEGE_GRADUATE,
            self::POST_GRADUATE,
            self::DOCTORATE,
        ];

        $rank = array_search($value, $ladder, true);

        return $rank === false ? 0 : (int) $rank;
    }
}
