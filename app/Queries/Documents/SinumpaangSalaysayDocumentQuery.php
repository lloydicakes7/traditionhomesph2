<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Sinumpaang Salaysay — a sworn statement, in Filipino, of what happened
 * between two homeowners.
 *
 * The statement is the affiant's own words: the office types them in, and they
 * are wrapped onto the thirteen ruled lines the paper carries. What is left of
 * the area stays ruled and empty, for the affiant to write on if they need the
 * room — a short story does not shrink the sheet.
 *
 * The Filipino month and day-part names live here because they are printed into
 * the statement itself: the month the incident happened in, and whether it was
 * morning, noon, afternoon or night.
 */
class SinumpaangSalaysayDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = [
        'case_no',
        'affiant_name',
        'affiant_age',
        'affiant_address',
        'respondent_name',
        'respondent_age',
        'respondent_address',
        'incident_date',
        'narrative',
    ];

    /** The ruled lines the statement's area carries. */
    const NARRATIVE_ROWS = 13;

    /** How many characters fit on one ruled line at 11 pt. */
    const CHARS_PER_LINE = 92;

    /**
     * The months as the statement writes them.
     *
     * @var array<int, string>
     */
    const MONTHS = [
        1  => 'Enero',
        2  => 'Pebrero',
        3  => 'Marso',
        4  => 'Abril',
        5  => 'Mayo',
        6  => 'Hunyo',
        7  => 'Hulyo',
        8  => 'Agosto',
        9  => 'Setyembre',
        10 => 'Oktubre',
        11 => 'Nobyembre',
        12 => 'Disyembre',
    ];

    /** How the paper names the part of the day a time falls in. */
    const DAY_PARTS = [
        'umaga'   => [5, 11],
        'tanghali' => [12, 13],
        'hapon'   => [14, 17],
        'gabi'    => [18, 4],
    ];

    /** The civil status the statement offers for the affiant. */
    const CIVIL_STATUSES = [
        'may-asawa' => 'may-asawa',
        'binata'    => 'binata',
        'dalaga'    => 'dalaga',
    ];

    /** What follows the unit in an address. */
    const ADDRESS_TAIL = 'Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite';

    /**
     * The view data for one statement.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function build($record): array
    {
        $params = $this->params($record);
        $affiant = $this->resident($params['residentId'] ?? null);
        $respondent = $this->resident($params['respondentId'] ?? null);
        $incident = DocumentText::dateParts($params['incidentDate'] ?? null);
        $execution = DocumentText::dateParts($params['executionDate'] ?? now());

        $data = $this->blank();

        $data['case_no'] = DocumentText::clean($params['caseNumber'] ?? '');
        $data['case_title'] = DocumentText::clean($params['caseTitle'] ?? '');

        $data['affiant_name'] = $this->name($affiant);
        $data['affiant_age'] = DocumentText::clean($affiant ? $affiant->age : '');
        $data['affiant_civil_status'] = $this->civilStatus($affiant);
        $data['affiant_address'] = $this->address($affiant);

        $data['respondent_name'] = $this->name($respondent);
        $data['respondent_age'] = DocumentText::clean($respondent ? $respondent->age : '');
        $data['respondent_address'] = $this->address($respondent);

        $data['incident_date'] = $incident;
        $data['incident_month'] = $this->month($params['incidentDate'] ?? null);
        $data['incident_time'] = $this->time($params['incidentTime'] ?? '');
        $data['incident_day_part'] = $this->dayPart($params['incidentTime'] ?? '');

        $data['narrative'] = $this->narrative(DocumentText::clean($params['narrative'] ?? ''));
        $data['narrative_rows'] = self::NARRATIVE_ROWS;

        $data['execution_date'] = $execution;
        $data['execution_month'] = $this->month($params['executionDate'] ?? now());
        $data['execution_phrase'] = $this->executionPhrase($execution, $params['executionDate'] ?? now());

        return $data;
    }

    /**
     * The statement exactly as the paper prints it with nothing filled in.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [
            'case_no'               => '',
            'case_title'            => '',
            'affiant_name'          => '',
            'affiant_age'           => '',
            'affiant_civil_status'  => '',
            'affiant_address'       => '',
            'respondent_name'       => '',
            'respondent_age'        => '',
            'respondent_address'    => '',
            'incident_date'         => DocumentText::dateParts(null),
            'incident_month'        => '',
            'incident_time'         => '',
            'incident_day_part'     => '',
            'narrative'             => [],
            'narrative_rows'        => self::NARRATIVE_ROWS,
            'execution_date'        => DocumentText::dateParts(null),
            'execution_month'       => '',
            'execution_phrase'      => '',
        ];
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * The affiant's civil status, as the statement's three words read it.
     */
    private function civilStatus(?Resident $resident): string
    {
        if (! $resident) {
            return '';
        }

        $status = strtolower(DocumentText::clean($resident->civil_status));
        $female = strtolower(DocumentText::clean($resident->sex)) === 'female';

        if ($status === 'married') {
            return 'may-asawa';
        }

        if ($status === 'single') {
            return $female ? 'dalaga' : 'binata';
        }

        return '';
    }

    /**
     * A name as the statement writes it.
     */
    private function name(?Resident $resident): string
    {
        if (! $resident) {
            return '';
        }

        return DocumentText::person(
            $resident->surname,
            $resident->first_name,
            $resident->middle_name,
            $resident->suffix
        );
    }

    /**
     * A party's address: their unit, then the subdivision it stands in.
     */
    private function address(?Resident $resident): string
    {
        $property = $this->propertyOf($resident);

        $unit = DocumentText::blockLot(
            $property && $property->block !== null ? (int) $property->block : null,
            $property && $property->lot !== null ? (int) $property->lot : null
        );

        if ($unit['label'] === '') {
            return '';
        }

        return $unit['label'] . ', ' . self::ADDRESS_TAIL;
    }

    /**
     * The month a date falls in, in Filipino.
     *
     * @param  mixed  $value
     */
    private function month($value): string
    {
        $date = $this->carbon($value);

        return $date ? self::MONTHS[(int) $date->format('n')] : '';
    }

    /**
     * The hour the statement swears to, as a clock time: "9:30".
     */
    private function time(string $time): string
    {
        $date = $this->carbon($time);

        return $date ? $date->format('g:i') : '';
    }

    /**
     * The part of the day the hour falls in: umaga, tanghali, hapon or gabi.
     */
    private function dayPart(string $time): string
    {
        $date = $this->carbon($time);

        if (! $date) {
            return '';
        }

        $hour = (int) $date->format('G');

        foreach (self::DAY_PARTS as $name => $range) {
            [$from, $to] = $range;

            if ($from <= $to ? ($hour >= $from && $hour <= $to) : ($hour >= $from || $hour <= $to)) {
                return $name;
            }
        }

        return '';
    }

    /**
     * The narrative, wrapped onto the ruled lines.
     *
     * One paragraph per line as the office typed it, wrapped to the width of a
     * ruled line. Every line is kept: a story longer than the area continues on
     * the ruled lines below rather than being cut short.
     *
     * @return array<int, string>
     */
    private function narrative(string $narrative): array
    {
        if ($narrative === '') {
            return [];
        }

        $lines = [];

        foreach (preg_split('/\R/u', $narrative) ?: [] as $paragraph) {
            $paragraph = DocumentText::clean($paragraph);

            if ($paragraph === '') {
                continue;
            }

            foreach (DocumentText::wrap($paragraph, self::CHARS_PER_LINE, self::NARRATIVE_ROWS) as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * The date the statement was sworn, as item four writes it: "ika-30 ng
     * Setyembre 2026".
     *
     * @param  array<string, string>  $parts
     * @param  mixed  $value
     */
    private function executionPhrase(array $parts, $value): string
    {
        if ($parts['day'] === '') {
            return '';
        }

        return $parts['day'] . ' ng ' . $this->month($value) . ' ' . $parts['year'];
    }

    /**
     * A value read as a date, or null when it is empty or unreadable.
     *
     * @param  mixed  $value
     */
    private function carbon($value): ?\Carbon\Carbon
    {
        if ($value instanceof \Carbon\Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value);
        }

        $value = DocumentText::clean($value);

        if ($value === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
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
     * The unit a party lives in.
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
