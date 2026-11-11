<?php

namespace App\Queries\Homeowners\Households;

use App\Models\Address;
use App\Models\Property;

/**
 * What the office already holds for a unit named by its block and lot.
 *
 * A walk-in resident is placed by the address they give at the desk. This reads
 * that address back from the office's own records — the unit, the household
 * living in it and who heads it — so linking to an existing household or
 * creating a new one is decided from what is on file rather than from what the
 * officer remembers.
 */
class HouseholdUnitQuery
{
    /**
     * The unit at this block and lot, with the household that holds it.
     *
     * @return array<string, mixed>|null
     */
    public function atUnit(int $block, int $lot): ?array
    {
        $property = Property::query()
            ->atUnit($block, $lot)
            ->with([
                'address',
                'currentHousehold.headMember.resident',
                'currentHousehold.memberRecords',
            ])
            ->first();

        if ($property === null) {
            return null;
        }

        $household = $property->currentHousehold;
        $head = $household ? $household->head_resident : null;

        return [
            'property_id'  => $property->id,
            'address'      => $property->full_address_label,
            'is_occupied'  => $household !== null,
            'household_id' => $household ? $household->id : null,
            'head_name'    => $head ? $head->full_name : null,
            'member_count' => $household ? $household->memberRecords->count() : 0,
        ];
    }

    /**
     * The association's own address — the one every unit stands in.
     */
    public function associationAddress(): ?Address
    {
        return Address::query()->primary()->first();
    }
}
