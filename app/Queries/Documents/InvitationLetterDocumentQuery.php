<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Invitation Letter.
 *
 * One framed sheet per household the association calls to a meeting. The letter
 * may go to a single homeowner or to every household head with an account; each
 * recipient is a whole sheet of the same words.
 */
class InvitationLetterDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = ['addressee_name', 'block', 'lot', 'meeting_date', 'meeting_time'];

    /** The most sheets one printed stack may hold. */
    const MAX_RECIPIENTS = 200;

    /** Characters one address blank holds at 11 pt across 95 mm. */
    const ADDRESS_CHARS = 52;

    /**
     * The view data for one meeting's invitations.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function build($record): array
    {
        $params = $this->params($record);

        $recipients = $this->recipients(
            $this->flag($params['allHomeowners'] ?? false),
            $this->resident($params['residentId'] ?? null)
        );

        $meeting = DocumentText::dateParts($params['meetingDate'] ?? null);
        $time = DocumentText::clean($params['meetingTime'] ?? null);

        $data = $this->blank();

        $data['recipients'] = $recipients;
        $data['addressee_name'] = $recipients[0]['name'] ?? '';
        $data['block'] = $recipients[0]['block'] ?? '';
        $data['lot'] = $recipients[0]['lot'] ?? '';
        $data['date'] = DocumentText::dateParts($params['letterDate'] ?? now());
        $data['meeting_date'] = $meeting;
        $data['meeting_time'] = $this->timeText($time);
        $data['day_part'] = $this->dayPart($time);
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
            'recipients'     => [$this->emptyRecipient()],
            'addressee_name' => '',
            'block'          => '',
            'lot'            => '',
            'date'           => DocumentText::dateParts(null),
            'meeting_date'   => DocumentText::dateParts(null),
            'meeting_time'   => '',
            'day_part'       => '',
            'control_no'     => '',
            'or_no'          => '',
        ];
    }

    // =====================================================================
    // Recipients
    // =====================================================================

    /**
     * Who the letter is for: one homeowner, or every household head.
     *
     * @return array<int, array<string, string>>
     */
    private function recipients(bool $everyone, ?Resident $resident): array
    {
        if (! $everyone) {
            return [$resident ? $this->recipient($resident) : $this->emptyRecipient()];
        }

        $residents = Resident::query()
            ->whereHas('user')
            ->with(['user.household.property', 'household.property'])
            ->orderBy('surname')
            ->orderBy('first_name')
            ->limit(self::MAX_RECIPIENTS)
            ->get();

        return $residents->map(function (Resident $homeowner) {
            return $this->recipient($homeowner);
        })->all() ?: [$this->emptyRecipient()];
    }

    /**
     * One recipient's sheet data.
     *
     * @return array<string, string>
     */
    private function recipient(Resident $resident): array
    {
        $property = $this->propertyOf($resident);
        $unit = DocumentText::blockLot(
            $property && $property->block !== null ? (int) $property->block : null,
            $property && $property->lot !== null ? (int) $property->lot : null
        );

        $address = $unit['label'] === ''
            ? ''
            : $unit['label'] . ', Phase 2, Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite';
        $lines = DocumentText::wrap($address, self::ADDRESS_CHARS, 2);

        return [
            'name'      => DocumentText::fit(DocumentText::person($resident->surname, $resident->first_name, $resident->middle_name, $resident->suffix), self::ADDRESS_CHARS),
            'block'     => $unit['block'],
            'lot'       => $unit['lot'],
            'address'   => $address,
            'address_1' => DocumentText::fit($lines[0] ?? '', self::ADDRESS_CHARS),
            'address_2' => DocumentText::fit(implode(' ', array_slice($lines, 1)), self::ADDRESS_CHARS),
        ];
    }

    /**
     * A recipient with nothing on file.
     *
     * @return array<string, string>
     */
    private function emptyRecipient(): array
    {
        return ['name' => '', 'block' => '', 'lot' => '', 'address' => '', 'address_1' => '', 'address_2' => ''];
    }

    // =====================================================================
    // Time
    // =====================================================================

    /**
     * The time as the letter writes it: "3:00 PM".
     */
    private function timeText(string $time): string
    {
        $hour = $this->hour($time);

        if ($hour === null) {
            return '';
        }

        $minute = (int) substr($time, -2);

        return date('g:i A', mktime($hour, $minute, 0, 1, 1, 2020));
    }

    /**
     * The part of the day the meeting falls in, as the letter's choice prints.
     */
    private function dayPart(string $time): string
    {
        $hour = $this->hour($time);

        if ($hour === null) {
            return '';
        }

        return $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');
    }

    /**
     * The hour of an "H:i", "g:i A" or ISO time, or null when it cannot be read.
     */
    private function hour(string $time): ?int
    {
        $time = trim($time);

        if ($time === '') {
            return null;
        }

        try {
            return (int) \Carbon\Carbon::parse($time)->format('G');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * A yes/no parameter, however the form spelled it.
     *
     * @param  mixed  $value
     */
    private function flag($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
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
     * The homeowner the letter is addressed to, with their unit loaded.
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
