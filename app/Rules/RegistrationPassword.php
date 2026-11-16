<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * The password every applicant must choose on the public register page.
 *
 * The office policy is still held to on top of this. A public form gets its own
 * floor so the criteria it prints are the ones it enforces, whatever the office
 * has configured for the doors behind it.
 */
class RegistrationPassword implements Rule
{
    /** The shortest password the register page accepts. */
    const MIN_LENGTH = 8;

    /** @var string The message shown when a criterion is not met */
    private $message = '';

    /**
     * Determine if the value meets the register page's four criteria.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = (string) $value;

        $meets = mb_strlen($value) >= self::MIN_LENGTH
            && preg_match('/[A-Z]/', $value) === 1
            && preg_match('/[a-z]/', $value) === 1
            && preg_match('/[0-9]/', $value) === 1
            && preg_match('/[^A-Za-z0-9]/', $value) === 1;

        if (! $meets) {
            $this->message = 'The :attribute must be at least ' . self::MIN_LENGTH
                . ' characters and include an uppercase letter, a lowercase letter, a number and a special character.';
        }

        return $meets;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
