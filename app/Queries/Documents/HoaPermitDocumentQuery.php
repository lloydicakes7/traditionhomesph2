<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Homeowners Association Permit.
 *
 * The permit states one thing: the association has no objection to what the
 * homeowner is about to do, for how long, and where. The paper carries two
 * blanks under the address and two under the purpose: a short value sits on the
 * first and the second prints empty, which is how a hand-filled permit looks.
 */
class HoaPermitDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = ['granted_to', 'block', 'lot', 'purpose', 'issued_date'];

    /** What the letter says after the address. */
    const ADDRESS_TAIL = 'Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite';

    /** Characters one permit blank holds at 11 pt across the content column. */
    const BLANK_CHARS = 74;

    /**
     * The view data for one permit.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function build($record): array
    {
        $params = $this->params($record);
        $resident = $this->resident($params['residentId'] ?? null);
        $property = $this->propertyOf($resident);

        $block = ($property && $property->block !== null) ? (int) $property->block : null;
        $lot = ($property && $property->lot !== null) ? (int) $property->lot : null;
        $unit = DocumentText::blockLot($block, $lot);

        $purpose = DocumentText::clean($params['purpose'] ?? '');
        $lines = DocumentText::wrap($purpose, self::BLANK_CHARS, 2);

        $data = $this->blank();

        $data['granted_to'] = $this->grantedTo($params, $resident);
        $data['block'] = $unit['block'];
        $data['lot'] = $unit['lot'];
        $address = $unit['label'] !== '' ? $unit['label'] . ', ' . self::ADDRESS_TAIL : '';
        $addressLines = DocumentText::wrap($address, self::BLANK_CHARS, 2);

        $data['address_line_1'] = $addressLines[0] ?? '';
        $data['address_line_2'] = implode(' ', array_slice($addressLines, 1));
        $data['purpose'] = $purpose;
        $data['purpose_line_1'] = $lines[0] ?? '';
        $data['purpose_line_2'] = DocumentText::fit(implode(' ', array_slice($lines, 1)), self::BLANK_CHARS);

        if (count($lines) > 2) {
            \Illuminate\Support\Facades\Log::warning('A permit purpose needed more lines than the paper carries.');
        }
        $data['location'] = DocumentText::clean($params['location'] ?? '');
        $data['event_period'] = $this->period($params['eventFrom'] ?? null, $params['eventTo'] ?? null);
        $data['issued_date'] = DocumentText::dateParts($params['issuedAt'] ?? now());
        $data['control_no'] = DocumentText::clean($params['controlNumber'] ?? '');
        $data['or_no'] = DocumentText::clean($params['orNumber'] ?? '');

        return $data;
    }

    /**
     * The permit exactly as the paper prints it with nothing filled in.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [
            'granted_to'     => '',
            'block'          => '',
            'lot'            => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'purpose'        => '',
            'purpose_line_1' => '',
            'purpose_line_2' => '',
            'location'       => '',
            'event_period'   => '',
            'issued_date'    => DocumentText::dateParts(null),
            'control_no'     => '',
            'or_no'          => '',
        ];
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * Who the permit is granted to: the officer's own wording when the form
     * gives one, else the resident's name.
     *
     * @param  array<string, mixed>  $params
     */
    private function grantedTo(array $params, ?Resident $resident): string
    {
        $given = DocumentText::clean($params['grantedTo'] ?? '');

        if ($given !== '') {
            return $given;
        }

        return $resident
            ? DocumentText::person($resident->surname, $resident->first_name, $resident->middle_name, $resident->suffix)
            : '';
    }

    /**
     * A span of days as the permit writes it: "October 5 to October 19, 2026".
     *
     * @param  mixed  $from
     * @param  mixed  $to
     */
    private function period($from, $to): string
    {
        $start = DocumentText::dateParts($from);
        $end = DocumentText::dateParts($to);

        if ($start['long'] === '') {
            return '';
        }

        if ($end['long'] === '' || $end['long'] === $start['long']) {
            return $start['long'];
        }

        if ($start['year'] === $end['year']) {
            return $start['month'] . ' ' . $start['day'] . ' to ' . $end['long'];
        }

        return $start['long'] . ' to ' . $end['long'];
    }

    // =====================================================================
    // Records
    // =====================================================================

    /**
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    private function params($record): array
    {
        if (is_array($record)) {
            return $record;
        }

        return $record instanceof Model ? $record->getAttributes() : [];
    }

    /**
     * The homeowner the permit names, with the unit loaded.
     */
    private function resident($residentId): ?Resident
    {
        if (! is_numeric($residentId) || (int) $residentId < 1) {
            return null;
        }

        return Resident::query()
            ->with(['user.household.property', 'household.property'])
            ->find((int) $residentId);
    }

    /**
     * The unit the resident lives in.
     */
    private function propertyOf(?Resident $resident)
    {
        if (! $resident) {
            return null;
        }

        $household = $resident->household ?: ($resident->user ? $resident->user->household : null);

        return $household ? $household->property : $resident->unit();
    }

}
