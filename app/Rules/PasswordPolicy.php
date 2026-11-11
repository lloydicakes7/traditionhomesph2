<?php

namespace App\Rules;

use App\Queries\Settings\SecuritySettingsQuery;
use Illuminate\Contracts\Validation\Rule;

/**
 * The password policy the office configured in Settings → Security.
 *
 * @see \App\Queries\Settings\SecuritySettingsQuery::passwordRequirements()
 */
class PasswordPolicy implements Rule
{
    /** @var string The message shown when the policy is not met */
    private $message = '';

    /**
     * Determine if the value satisfies the configured password policy.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $requirements = $this->requirements();

        $longEnough = mb_strlen((string) $value) >= $requirements['min_length'];

        $missing = array_keys(array_filter([
            'an uppercase letter' => $requirements['upper'] && preg_match('/[A-Z]/', (string) $value) !== 1,
            'a lowercase letter'  => $requirements['lower'] && preg_match('/[a-z]/', (string) $value) !== 1,
            'a number'            => $requirements['number'] && preg_match('/[0-9]/', (string) $value) !== 1,
            'a symbol'            => $requirements['symbol'] && preg_match('/[^A-Za-z0-9]/', (string) $value) !== 1,
        ]));

        if ($longEnough && empty($missing)) {
            return true;
        }

        $this->message = 'The :attribute must be at least ' . $requirements['min_length'];

        if (! empty($missing)) {
            $this->message .= ' and include ' . implode(', ', $missing) . '.';
        }

        return false;
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

    /**
     * The configured requirements, resolved through the security service.
     *
     * @return array{min_length: int, upper: bool, lower: bool, number: bool, symbol: bool}
     */
    private function requirements(): array
    {
        return app(SecuritySettingsQuery::class)->passwordRequirements();
    }
}
