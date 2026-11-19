<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * The fixed password protocol used wherever an account sets a password.
 */
class PasswordPolicy implements Rule
{
    const MIN_LENGTH = 8;

    /** @var string The message shown when the protocol is not met */
    private $message = '';

    /**
     * Determine whether the value meets every criterion.
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
