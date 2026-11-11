<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Endorsement Letter.
 *
 * The association endorses a homeowner's complaint to the barangay. The letter
 * names the complainant and the party complained of, with each one's unit on
 * an inline blank, and states the problem over two ruled lines.
 */
class EndorsementLetterDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = [
        'complainant_name',
        'complainant_block',
        'complainant_lot',
        'respondent_name',
        'respondent_block',
        'respondent_lot',
        'concern',
        'date',
    ];

    /** Characters one concern line holds at 11 pt across the content column. */
    const CONCERN_CHARS = 92;

    /**
     * The view data for one endorsement letter.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function build($record): array
    {
        $params = $this->params($record);

        $complainant = $this->resident($params['residentId'] ?? null);
        $respondent = $this->resident($params['respondentId'] ?? null);

        $complainantUnit = $this->unitOf($complainant);
        $respondentUnit = $this->unitOf($respondent);

        foreach (['block' => 'respondentBlock', 'lot' => 'respondentLot'] as $key => $param) {
            $given = DocumentText::clean($params[$param] ?? '');

            if ($given !== '') {
                $respondentUnit[$key] = $given;
            }
        }

        $concern = DocumentText::clean($params['problem'] ?? '');
        $lines = DocumentText::wrap($concern, self::CONCERN_CHARS, 2);

        $data = $this->blank();

        $data['complainant_name'] = $this->nameOf($complainant);
        $data['complainant_block'] = $complainantUnit['block'];
        $data['complainant_lot'] = $complainantUnit['lot'];
        $data['respondent_name'] = $this->nameOf($respondent);
        $data['respondent_block'] = $respondentUnit['block'];
        $data['respondent_lot'] = $respondentUnit['lot'];
        $data['concern'] = $concern;
        $data['concern_line_1'] = $lines[0] ?? '';
        $data['concern_line_2'] = DocumentText::fit(implode(' ', array_slice($lines, 1)), self::CONCERN_CHARS);

        if (count($lines) > 2) {
            \Illuminate\Support\Facades\Log::warning('An endorsement concern needed more lines than the paper carries.');
        }
        $data['date'] = DocumentText::dateParts($params['letterDate'] ?? now());
        $data['control_no'] = DocumentText::clean($params['controlNumber'] ?? '');
        $data['or_no'] = DocumentText::clean($params['orNumber'] ?? '');

        return $data;
    }

    /**
     * The letter exactly as the paper prints it with nothing filled in.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [
            'complainant_name'  => '',
            'complainant_block' => '',
            'complainant_lot'   => '',
            'respondent_name'   => '',
            'respondent_block'  => '',
            'respondent_lot'    => '',
            'concern'           => '',
            'concern_line_1'    => '',
            'concern_line_2'    => '',
            'date'              => DocumentText::dateParts(null),
            'control_no'        => '',
            'or_no'             => '',
        ];
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * A party's name as the letter writes it, "Surname, First Middle".
     */
    private function nameOf(?Resident $resident): string
    {
        return $resident
            ? DocumentText::person($resident->surname, $resident->first_name, $resident->middle_name, $resident->suffix)
            : '';
    }

    /**
     * A party's unit.
     *
     * @return array<string, string>
     */
    private function unitOf(?Resident $resident): array
    {
        $property = $resident ? $this->propertyOf($resident) : null;

        return DocumentText::blockLot(
            $property && $property->block !== null ? (int) $property->block : null,
            $property && $property->lot !== null ? (int) $property->lot : null
        );
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
     * One of the two parties, with their unit loaded.
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
     * The unit a resident lives in.
     */
    private function propertyOf(Resident $resident)
    {
        $household = $resident->household ?: ($resident->user ? $resident->user->household : null);

        return $household ? $household->property : $resident->unit();
    }

}
