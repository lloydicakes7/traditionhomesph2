<?php

namespace App\Queries\Homeowners\Households;

use App\Enums\HouseholdType;
use App\Models\Household\Household;

class HouseholdStatsQuery
{
    // ===== Summary =====

    public function summary(): array
    {
        return [
            'total'   => Household::count(),
            'owners'  => Household::where('type', HouseholdType::OWNER)->count(),
            'tenants' => Household::where('type', HouseholdType::TENANT)->count(),
            'others'  => Household::whereNotIn('type', [HouseholdType::OWNER, HouseholdType::TENANT])->count(),
        ];
    }
}
