<?php

namespace App\Queries\Homeowners\Residents;

use App\Models\Resident;

class ResidentOptionQuery
{
    // ===== Options =====

    /**
     * The residents an office form can name, as `id => "Name (Block 4 Lot 12)"`.
     *
     * @return array<int, string>
     */
    public function all(): array
    {
        return Resident::query()
            ->with(['household.property', 'user.household.property'])
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(function (Resident $resident) {
                $unit = $resident->unit();

                return [
                    $resident->id => $resident->full_name
                        . ($unit ? ' (' . $unit->block_lot_label . ')' : ''),
                ];
            })
            ->all();
    }
}
