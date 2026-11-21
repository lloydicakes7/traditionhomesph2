<?php

namespace App\Rules;

use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class HouseholdHeadAge implements Rule
{
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
        return 'A household head must be '.Resident::MINIMUM_HEAD_AGE.'+ years old.';
    }
}
