<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * A directory number: a hotline such as 911 or a full mobile number, written as
 * three to eleven digits.
 */
class DirectoryPhoneNumber implements Rule
{
    /**
     * Determine if the value is a directory number.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^\d{3,11}$/', (string) $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Enter a phone number of 3 to 11 digits.';
    }
}
