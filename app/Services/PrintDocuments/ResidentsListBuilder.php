<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Models\Address;
use App\Models\Property;
use App\Utility\PrintDocumentsHelper as Helper;
use Illuminate\Support\Collection;

/**
 * The Residents List — every household on file, in its unit.
 *
 * The one document that prints across the sheet, because a row is as wide as
 * the household is deep: the unit, then one line per member, the head first.
 * It is a report rather than a form, so it filters (by address, by block and by
 * the numbers the list is numbered with) and sorts (by block, by name, by unit
 * type) instead of being filled in.
 *
 * A row *is* a household list, so a unit nobody lives in is not one of its
 * rows: only the units whose household has a head or a member on file are read
 * — which is also what keeps the sheet short enough to draw.
 *
 * Every unit is read in one query with its address, its household, that
 * household's account holder and its member records — a list of a thousand
 * units costs the same few queries a list of one does.
 */
final class ResidentsListBuilder extends AbstractPrintDocumentBuilder
{
    /**
     * The units to list, with everything a row prints already loaded.
     *
     * @var array<int, string>
     */
    const PROPERTY_RELATIONS = [
        'address',
        'currentHousehold.user.resident',
        'currentHousehold.memberRecords.resident',
    ];

    /**
     * The most units one printed list may hold.
     *
     * An address can hold thousands of units, each with its household, and
     * dompdf holds the entire document in memory while it lays it out — past a
     * point that is more than a single sheet of paper's worth of PDF can carry.
     * The list stops at a number that prints comfortably and says so, so the
     * office prints the rest by choosing a block or a range of numbers.
     */
    const MAX_ROWS = 2000;

    /**
     * How the list may be ordered, as the filter offers it.
     *
     * @var array<string, string>
     */
    const SORTS = [
        'block' => 'Block, then Lot',
        'name'  => 'Name',
        'type'  => 'Type',
    ];

    /**
     * The residents list runs across the sheet — a household's row is wider
     * than the page is tall.
     */
    public function orientation(): string
    {
        return 'landscape';
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $addressId = $this->intOrNull($params, 'addressId');
        $block     = $this->stringOf($params, 'block');
        $sortBy    = $this->stringOf($params, 'sortBy', 'block');
        $from      = $this->numberOf($params, 'startNo') ?? 1;
        $to        = $this->numberOf($params, 'endNo');

        $properties = $this->properties($addressId, $block);
        $truncated  = $properties->count() > self::MAX_ROWS;

        $numbered = $this->rows($properties->take(self::MAX_ROWS)->values(), $sortBy);
        $rows     = $this->numberedBetween($numbered, $from, $to);

        return [
            'filters'     => [
                'address'   => $this->addressFilter($addressId),
                'block'     => $block !== '' ? $block : null,
                'sortLabel' => self::SORTS[$sortBy] ?? self::SORTS['block'],
                'numbers'   => $this->numbersLabel($rows, $numbered),
            ],
            'generatedAt' => now(),
            'rows'        => $rows,
            'truncated'   => $truncated,
            'limit'       => self::MAX_ROWS,
            'totals'      => [
                'properties' => $rows->count(),
                'residents'  => $rows->sum(function (array $row) {
                    return count($row['members']);
                }),
                'households' => $rows->where('headed', true)->count(),
            ],
        ];
    }

    // =====================================================================
    // The units
    // =====================================================================

    /**
     * Every matching unit that has a household list, ordered by the unit itself
     * — the order the list falls back on whatever else it is sorted by.
     *
     * A unit is listed only while its household has someone on file to print: a
     * head (the household's own account) or a member record. A unit standing
     * empty is not a row of this document, and reading a thousand of them to
     * draw "No household on file" a thousand times is what made the page slow.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Property>
     */
    private function properties(?int $addressId, string $block): Collection
    {
        return Property::query()
            ->with(self::PROPERTY_RELATIONS)
            ->whereHas('currentHousehold', function ($household) {
                $household->whereHas('user.resident')->orWhereHas('memberRecords');
            })
            ->when($addressId, function ($query) use ($addressId) {
                $query->where('address_id', $addressId);
            })
            ->when($block !== '', function ($query) use ($block) {
                $query->where('block', $block);
            })
            ->orderBy('block')
            ->orderBy('lot')
            ->limit(self::MAX_ROWS + 1)
            ->get();
    }

    /**
     * The units as numbered rows, ordered and numbered the way the list asked
     * for — the number is the row's place in the printed list, not the unit's
     * id.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Property>  $properties
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function rows(Collection $properties, string $sortBy): Collection
    {
        $rows = $properties->map(function (Property $property) {
            return [
                'blockLot' => Helper::blockLot($property),
                'typeArea' => $this->typeArea($property),
                'members'  => $this->members($property),
                'name'     => $this->sortName($property),
                'type'     => $property->type_enum ? $property->type_enum->label() : 'zzz',
                'headed'   => $this->headOf($property->currentHousehold) !== null,
            ];
        });

        if ($sortBy === 'name') {
            $rows = $rows->sortBy('name');
        } elseif ($sortBy === 'type') {
            $rows = $rows->sortBy('type');
        }

        return $rows->values()->map(function (array $row, int $index) {
            return [
                'no'       => $index + 1,
                'blockLot' => $row['blockLot'],
                'typeArea' => $row['typeArea'],
                'members'  => $row['members'],
                'headed'   => $row['headed'],
            ];
        });
    }

    /**
     * The numbered rows the sheet prints — the list narrowed to the numbers the
     * form asked for, both ends included.
     *
     * The numbers are the row's place in the whole ordered list, not its place
     * in this printout, so printing a list in batches numbers it the way one
     * sheet of it would: the first batch ends where the second one starts.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function numberedBetween(Collection $rows, int $from, ?int $to): Collection
    {
        if ($to === null) {
            return $rows->where('no', '>=', $from)->values();
        }

        return $rows->whereBetween('no', [$from, $to])->values();
    }

    /**
     * The numbers this sheet carries, as its meta line reads them: the span
     * printed, and how many units the filter holds in all.
     *
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $numbered
     */
    private function numbersLabel(Collection $rows, Collection $numbered): string
    {
        if ($rows->isEmpty()) {
            return 'None of ' . $numbered->count();
        }

        return $rows->first()['no'] . '–' . $rows->last()['no'] . ' of ' . $numbered->count();
    }

    /**
     * One unit's household, the head first.
     *
     * @return array<int, array<string, string>>
     */
    private function members(Property $property): array
    {
        $household = $property->currentHousehold;

        if (! $household) {
            return [];
        }

        $members = [];

        $head = $household->head_resident;

        if ($head) {
            $record = $household->memberRecords->firstWhere('resident_id', $head->id);

            $members[] = $this->person($head, $record ? $record->relationship_label : '—');
        }

        foreach ($household->memberRecords as $record) {
            $resident = $record->resident;

            if (! $resident || ($head && $resident->id === $head->id)) {
                continue;
            }

            $members[] = $this->person($resident, $this->relationship($record->relationship));
        }

        return $members;
    }

    /**
     * One member's line of a unit's row.
     *
     * @return array<string, string>
     */
    private function person($resident, string $relationship): array
    {
        $mobile = trim((string) $resident->mobile_no);

        return [
            'name'         => Helper::fullName($resident),
            'relationship' => $relationship,
            'sex'          => strtolower((string) $resident->sex) === 'female' ? 'F' : 'M',
            'age'          => $resident->age !== null ? (string) $resident->age : '',
            'contact'      => $mobile !== '' ? $mobile : '—',
        ];
    }

    /**
     * A unit's size and kind for the row's second column: "House · 120 sqm".
     */
    private function typeArea(Property $property): string
    {
        $parts = [];

        if ($property->type_enum) {
            $parts[] = $property->type_enum->label();
        } elseif ($property->type) {
            $parts[] = (string) $property->type;
        }

        if ($property->sqm !== null) {
            $parts[] = number_format((float) $property->sqm, 0) . ' sqm';
        }

        return implode(' · ', $parts);
    }

    /**
     * The surname a unit is filed under when the list is sorted by name — a
     * unit with no household sorts last.
     */
    private function sortName(Property $property): string
    {
        $head = $this->headOf($property->currentHousehold);

        return $head ? (string) $head->surname . ' ' . $head->first_name : 'zzz';
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
}
