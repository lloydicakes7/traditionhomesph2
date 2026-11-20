<?php

namespace App\Rules;

use App\Models\Resident;
use Illuminate\Contracts\Validation\Rule;

class HouseholdHeadResident implements Rule
{
    /**
     * Whether the selected resident is old enough to head a household.
     */
    public function passes($attribute, $value): bool
    {
        $resident = Resident::find((int) $value);

        return $resident !== null && $resident->canHeadHousehold();
    }

    public function message(): string
    {
        return 'A household head must be at least '.Resident::MINIMUM_HEAD_AGE.' years old.';
    }
}
