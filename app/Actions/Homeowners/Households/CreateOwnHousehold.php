<?php

namespace App\Actions\Homeowners\Households;

use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A homeowner creating their own household when the office has none on record.
 *
 * The portal never links to, or overwrites, a household that already exists:
 * a unit somebody lives in is refused here, and the office places the
 * homeowner. A unit the office has no record of is written under the
 * association's own address, and the homeowner who creates it becomes its head.
 */
class CreateOwnHousehold
{
    // ===== Handle =====

    public function handle(User $user, array $data): Household
    {
        $resident = Resident::find((int) $user->resident_id);

        if (! $resident) {
            throw new \RuntimeException('Your account has no resident record yet.');
        }

        if ($resident->household()->exists()) {
            throw new \RuntimeException('Your account already has a household.');
        }

        $property = $this->propertyFor((int) $data['block'], (int) $data['lot']);

        return DB::transaction(function () use ($property, $resident, $data) {
            $household = Household::create(Household::payloadFrom([
                'property_id'  => $property->id,
                'type'         => $data['type'],
                'prev_address' => $data['prev_address'] ?? null,
            ]));

            $household->placeHead($resident->id, $data['relationship'] ?? null);

            return $household;
        });
    }

    // ===== Helpers =====

    /**
     * The unit the household will live in: one already on file and empty, or a
     * unit the office has no record of, written under the association address.
     */
    private function propertyFor(int $block, int $lot): Property
    {
        $property = Property::query()->atUnit($block, $lot)->first();

        if ($property) {
            if ($property->currentHousehold()->exists()) {
                throw new \RuntimeException('Someone is already registered at this address.');
            }

            return $property;
        }

        $address = Address::query()->primary()->first();

        if ($address === null) {
            throw new \RuntimeException('The association address is not recorded.');
        }

        return Property::create([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
        ]);
    }
}
