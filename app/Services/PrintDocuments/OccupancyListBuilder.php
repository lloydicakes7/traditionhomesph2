<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\HouseholdType;
use App\Models\Address;
use App\Models\Property;
use App\Utility\PrintDocumentsHelper as Helper;
use Illuminate\Support\Collection;

/**
 * The Occupancy List — every unit on file, with the household that holds it.
 *
 * Where the residents list prints every person in a household, this is the
 * unit's own register: one row per occupied unit, naming who holds it and how
 * (owner, renter, informal settler or caretaker). The office picks one kind to
 * print a list of owners alone, renters alone, and so on; the closing line is
 * the one count the list needs, how many units it printed.
 *
 * A unit nobody occupies is not a row: the list reads only the units whose
 * household is on file, and reads them all in one query.
 */
final class OccupancyListBuilder extends AbstractPrintDocumentBuilder
{
    /**
     * The most units one printed list may hold — the same ceiling the
     * residents list uses, for the same reason: dompdf lays the whole sheet
     * out in memory before it writes a page.
     */
    const MAX_ROWS = 2000;

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $addressId = $this->intOrNull($params, 'addressId');
        $type      = $this->stringOf($params, 'occupantType');

        $properties = $this->properties($addressId, $type);
        $truncated  = $properties->count() > self::MAX_ROWS;

        $rows = $this->rows($properties->take(self::MAX_ROWS)->values());

        return [
            'filters' => [
                'address' => $this->addressFilter($addressId),
                'type'    => $this->typeFilter($type),
            ],
            'generatedAt' => now(),
            'rows'        => $rows,
            'truncated'   => $truncated,
            'limit'       => self::MAX_ROWS,
            'total'       => $rows->count(),
        ];
    }

    /**
     * Every occupied unit the filter allows — the unit that holds one household
     * of the chosen kind, or of any kind.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Property>
     */
    private function properties(?int $addressId, string $type): Collection
    {
        return Property::query()
            ->with(['currentHousehold.headMember.resident'])
            ->whereHas('currentHousehold', function ($household) use ($type) {
                if ($type !== '') {
                    $household->where('type', $type);
                }
            })
            ->when($addressId, function ($query) use ($addressId) {
                $query->where('address_id', $addressId);
            })
            ->orderBy('block')
            ->orderBy('lot')
            ->limit(self::MAX_ROWS + 1)
            ->get();
    }

    /**
     * The units as numbered rows, each printing who holds it.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Property>  $properties
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function rows(Collection $properties): Collection
    {
        return $properties->map(function (Property $property, int $index) {
            $household = $property->currentHousehold;
            $head      = $household ? $household->head_resident : null;

            return [
                'no'            => $index + 1,
                'blockLot'      => Helper::blockLot($property),
                'unit'          => $property->sqm_type_display,
                'occupant'      => $head ? Helper::fullName($head) : 'No head on file',
                'householdType' => $this->typeLabel($household),
                'contact'       => trim((string) ($head ? $head->mobile_no : '')) ?: '—',
            ];
        });
    }

    /**
     * One household's tenure, as the list names it.
     */
    private function typeLabel($household): string
    {
        return $household && $household->type_enum ? $household->type_enum->label() : '—';
    }

    /**
     * The address line the list says it was filtered by.
     */
    private function addressFilter(?int $addressId): string
    {
        if (! $addressId) {
            return 'All addresses';
        }

        $address = Address::find($addressId);

        return $address ? PrintDocumentOptions::addressLabel($address) : 'All addresses';
    }

    /**
     * The occupant kind the list says it was filtered by.
     */
    private function typeFilter(string $type): string
    {
        $label = $type !== '' ? HouseholdType::tryFrom($type) : null;

        return $label ? $label->label() : 'All occupants';
    }
}
