<?php

namespace App\Rules;

use App\Services\Homeowners\SectoralTagService;
use Illuminate\Contracts\Validation\Rule;

class SectoralTags implements Rule
{
    /** @var array<string, mixed> */
    private $facts;

    /** @var array<int, string> */
    private $messages = [];

    /**
     * @param  array<string, mixed>  $facts
     */
    public function __construct(array $facts)
    {
        $this->facts = $facts;
    }

    /**
     * Whether every chosen tag agrees with the person's age, work and studies.
     */
    public function passes($attribute, $value): bool
    {
        $this->messages = app(SectoralTagService::class)->errors((array) $value, $this->facts);

        return $this->messages === [];
    }

    public function message(): string
    {
        return implode(' ', $this->messages) ?: 'The sectoral tags do not match the rest of the profile.';
    }
}
