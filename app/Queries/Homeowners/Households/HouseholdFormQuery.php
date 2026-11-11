<?php

namespace App\Queries\Homeowners\Households;

use App\Enums\HouseholdType;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;

class HouseholdFormQuery
{
    // ===== Options =====

    public function options($householdId = null): array
    {
        $household = Household::with('headMember')->find($householdId);
        $headResidentId = $household?->head_resident?->id;

        return [
            'residents'      => Resident::availableForHousehold($headResidentId)
                ->orderBy('surname')
                ->orderBy('first_name')
                ->get(),
            'properties'     => Property::availableFor($household?->id)
                ->with('address')
                ->orderBy('block')
                ->orderBy('lot')
                ->get(),
            'householdTypes' => HouseholdType::labels(),
        ];
    }

    public function blank(): array
    {
        return [
            'residents'      => collect(),
            'properties'     => collect(),
            'householdTypes' => [],
        ];
    }
}
