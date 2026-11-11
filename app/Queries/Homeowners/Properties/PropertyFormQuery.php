<?php

namespace App\Queries\Homeowners\Properties;

use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Models\Address;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;

class PropertyFormQuery
{
    // ===== Options =====

    public function options(): array
    {
        return [
            'addresses'      => Address::orderBy('label')->get(['id', 'label', 'subdivision', 'barangay', 'city']),
            'residents'      => $this->availableResidents(),
            'types'          => PropertyType::labels(),
            'householdTypes' => HouseholdType::labels(),
        ];
    }

    public function availableResidents(): Collection
    {
        return Resident::availableForHousehold()
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get();
    }

    public function blank(): array
    {
        return [
            'addresses'      => collect(),
            'residents'      => collect(),
            'types'          => [],
            'householdTypes' => [],
            'relationships'  => [],
        ];
    }
}
