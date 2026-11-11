<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Household\Household;

class UpdateHouseholdFacts
{
    // ===== Handle =====

    public function handle(Household $household, array $data): Household
    {
        $household->update([
            'type'         => $data['type'],
            'prev_address' => $this->blankToNull($data['prev_address'] ?? null),
        ]);

        return $household;
    }

    // ===== Helpers =====

    private function blankToNull($value)
    {
        return $value === '' || $value === null ? null : $value;
    }
}
