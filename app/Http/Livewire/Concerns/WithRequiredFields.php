<?php

namespace App\Http\Livewire\Concerns;

/**
 * The fields a component validates as required, named for the browser.
 *
 * The names travel in the component snapshot so the shared script can mark the
 * matching labels without every form repeating its own required list.
 */
trait WithRequiredFields
{
    // ===== Setup =====

    /** @var array<int, string> */
    public $requiredFields = [];

    public function dehydrate(): void
    {
        $this->requiredFields = $this->requiredFieldNames();
    }

    // ===== Helpers =====

    /**
     * The required fields the rules name, read while the view is rendered too.
     *
     * @return array<int, string>
     */
    public function requiredFieldNames(): array
    {
        $fields = [];

        foreach ($this->getRules() as $field => $rules) {
            foreach ((array) $rules as $rule) {
                $required = is_string($rule)
                    ? $this->hasRequiredToken($rule)
                    : method_exists($rule, '__toString') && (string) $rule === 'required';

                if ($required) {
                    $fields[] = $field;

                    break;
                }
            }
        }

        return $fields;
    }

    private function hasRequiredToken(string $rule): bool
    {
        foreach (explode('|', $rule) as $token) {
            if (strpos(trim($token), 'required') === 0) {
                return true;
            }
        }

        return false;
    }
}
