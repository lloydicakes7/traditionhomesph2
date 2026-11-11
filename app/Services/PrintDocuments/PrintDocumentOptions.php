<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\JournalEntryStatus;
use App\Models\Address;
use App\Models\JournalEntry\JournalEntry;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Violation;

/**
 * The pickers the print pages offer, and the labels they read.
 *
 * A print page collects the *record* a document is about — which resident,
 * which unit, which journal entry, which violation — so the lists those selects
 * are filled from live here rather than in the pages, and every page that needs
 * one reads the same list. Nothing here builds a document; it only says what a
 * form may choose from.
 */
class PrintDocumentOptions
{
    /**
     * How a mailing address reads in a picker or a filter: its own label where
     * the office gave it one, else the street it stands on.
     *
     * One method, so the option a user picks and the filter line the document
     * prints cannot describe the same address two ways.
     */
    public static function addressLabel(Address $address): string
    {
        $label = trim((string) $address->label);

        if ($label !== '') {
            return $label;
        }

        $street = trim((string) $address->street_line);

        return $street !== '' ? $street : 'Address #' . $address->id;
    }

    /**
     * Residents as a select's options — the person a document is about.
     *
     * @return array<int, string>
     */
    public function residentOptions(): array
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

    /**
     * Addresses as a select's options.
     *
     * @return array<int, string>
     */
    public function addressOptions(): array
    {
        return Address::query()
            ->orderBy('street')
            ->get()
            ->mapWithKeys(function (Address $address) {
                return [$address->id => self::addressLabel($address)];
            })
            ->all();
    }

    /**
     * Journal entries as a select's options, newest first.
     *
     * @return array<int, string>
     */
    public function journalEntryOptions(): array
    {
        return JournalEntry::query()
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->mapWithKeys(function (JournalEntry $entry) {
                return [$entry->id => $entry->entry_no . ' · ' . $entry->entry_date_display];
            })
            ->all();
    }

    /**
     * The years the books cover, newest first — what a report drawn for a whole
     * year is asked to choose from.
     *
     * The span runs from the association's first posted entry to its last, and
     * always includes the current year, so a year the office has not posted in
     * yet can still be asked for. It is read with the driver's own MIN/MAX of
     * the entry date, so the same two queries answer on MySQL and on the SQLite
     * the test suite runs on.
     *
     * @return array<int, string>
     */
    public function yearOptions(): array
    {
        $posted = JournalEntry::query()->where('status', JournalEntryStatus::POSTED);

        $first = (int) substr((string) $posted->min('entry_date'), 0, 4);
        $last = (int) substr((string) $posted->max('entry_date'), 0, 4);

        $thisYear = (int) date('Y');

        $oldest = min($first > 0 ? $first : $thisYear, $thisYear);
        $newest = max($last, $thisYear);

        $years = [];

        for ($year = $newest; $year >= $oldest; $year--) {
            $years[] = (string) $year;
        }

        return $years;
    }

    /**
     * Violations as a select's options, newest first.
     *
     * @return array<int, string>
     */
    public function violationOptions(): array
    {
        return Violation::query()
            ->with('resident')
            ->ordered()
            ->limit(200)
            ->get()
            ->mapWithKeys(function (Violation $violation) {
                return [
                    $violation->id => $violation->type_label
                        . ' · ' . ($violation->resident ? $violation->resident->full_name : $violation->resident_name)
                        . ' · ' . ($violation->occurred_on ? $violation->occurred_on->format('M j, Y') : ''),
                ];
            })
            ->all();
    }

    /**
     * The blocks on file, for the residents list's filter.
     *
     * A block is the association's own label ("Block 9"), so it is offered and
     * matched as the text it is — reading it as a number would collapse every
     * block onto one key and leave the filter showing a single block.
     *
     * @return array<string, string>
     */
    public function blockOptions(): array
    {
        return Property::query()
            ->whereNotNull('block')
            ->distinct()
            ->orderBy('block')
            ->pluck('block')
            ->mapWithKeys(function ($block) {
                return [(string) $block => (string) $block];
            })
            ->all();
    }
}
