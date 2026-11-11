<?php

namespace App\Rules;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * An email address no account of either type holds yet.
 *
 * The two account tables are separate, so `unique:users` alone would let a
 * homeowner register an address an officer already signs in with — and the
 * unified login would then have to guess which account was meant.
 */
class EmailAvailable implements Rule
{
    /** @var string */
    private $message = '';

    /**
     * Determine if the email address is free on both account tables.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $email = strtolower(trim((string) $value));

        if ($email === '') {
            return false;
        }

        if (User::where('email', $email)->exists()) {
            $this->message = 'That email address is already registered.';

            return false;
        }

        if (Admin::where('email', $email)->exists()) {
            $this->message = 'That email address is already registered.';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?: 'That email address is already registered.';
    }
}
