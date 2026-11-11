<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * A mobile number in the format the office accepts: 09XXXXXXXXX, optionally
 * written with spaces or dashes, or the +639XXXXXXXXX form.
 */
class PhilippineMobileNumber implements Rule
{
    /**
     * Determine if the value is a Philippine mobile number.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $digits = preg_replace('/[\s\-()]/', '', (string) $value);

        return preg_match('/^(09|\+639)\d{9}$/', $digits) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid Philippine mobile number (e.g. 09171234567).';
    }
}
