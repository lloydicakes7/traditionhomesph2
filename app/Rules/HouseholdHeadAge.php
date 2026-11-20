<?php

namespace App\Rules;

use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class HouseholdHeadAge implements Rule
{
    /** @var string|null */
    private $message;

    /**
     * @param  string|null  $message  The sentence shown when the resident is too young
     */
    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }

    /**
     * Whether the birth date belongs to a resident old enough to head a household.
     */
    public function passes($attribute, $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return Carbon::parse((string) $value)->age >= Resident::MINIMUM_HEAD_AGE;
    }

    public function message(): string
    {
        return $this->message
            ?: 'A household head must be at least '.Resident::MINIMUM_HEAD_AGE.' years old.';
    }
}
