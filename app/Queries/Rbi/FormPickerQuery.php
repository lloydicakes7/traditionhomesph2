<?php

namespace App\Queries\Rbi;

use App\Models\Household\Household;
use App\Models\Resident;

/**
 * The records the admin RBI pages choose from, as their select2 options.
 *
 * Form A picks a household and Form B a resident; each list is read here so the
 * pages only render it.
 */
class FormPickerQuery
{
    // ===== Options =====

    /**
     * Households, named by their unit and the family that holds it.
     *
     * @return array<int, string>
     */
    public function households(): array
    {
        return Household::query()
            ->with('property', 'headMember.resident')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (Household $household) {
                $unit = $household->property ? $household->property->block_lot_label . ' · ' : '';

                return [$household->id => $unit . $household->display_name];
            })
            ->all();
    }

    /**
     * Residents, named in one ordering.
     *
     * @return array<int, string>
     */
    public function residents(): array
    {
        return Resident::query()
            ->orderBy('surname')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(function (Resident $resident) {
                return [$resident->id => $resident->full_name];
            })
            ->all();
    }
}
