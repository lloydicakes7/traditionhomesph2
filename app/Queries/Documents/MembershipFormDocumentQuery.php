<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Utility\DocumentText;
use App\Utility\MembershipFormDetails;
use Illuminate\Database\Eloquent\Model;

/**
 * The Membership Form, as the office prints it.
 *
 * One page per household member: who the member is, who they live with, how the
 * unit is held. Everything is read from the resident's own record and the
 * household it belongs to; the office chooses the member and the date.
 *
 * The household table on the paper carries thirteen rows whatever is in the
 * record, so a form with two members still looks like the printed form and the
 * structure of a blank and a filled sheet is identical. A household with more
 * than thirteen members continues on a second sheet carrying the same header.
 */
class MembershipFormDocumentQuery
{
    /** The rows the household table carries on its first sheet. */
    const MEMBER_ROWS = 13;

    /** How the unit is held, in the order the paper prints the boxes. */
    const MEMBER_TYPES = ['Owner', 'Renter', 'Caretaker', 'Informal Settler'];

    /** The civil statuses the paper offers. */
    const CIVIL_STATUSES = ['Single', 'Married', 'Separated', 'Widow'];

    /**
     * The view data for one member's form.
     *
     * The print form supplies a validated array of parameters; a record may be
     * handed instead, which is what the next batches' tests and any future
     * "print this member" action use. Both resolve to the same shape.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function build($record): array
    {
        return $this->preview($record);
    }

    /**
     * The same view data, whether or not the record fills every required blank.
     *
     * The request form shows the member's details before the form is filed, so
     * the sheet has to be readable without refusing it; only `build()` enforces
     * the blanks the paper cannot be printed without.
     *
     * @param  \Illuminate\Database\Eloquent\Model|array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function preview($record): array
    {
        $params = $this->withSuppliedForRequest($this->params($record));
        $resident = $this->resident($params['residentId'] ?? null);
        $household = $this->householdOf($resident);
        $property = $household ? $household->property : null;
        $property = $property ?: ($resident ? $resident->unit() : null);

        $data = $this->blank();

        $data['date'] = DocumentText::dateParts($params['dateAccomplished'] ?? now());
        $data['surname'] = DocumentText::clean($resident ? $resident->surname : '');
        $data['first_name'] = DocumentText::clean($resident ? $resident->first_name : '');
        $data['middle_name'] = DocumentText::clean($resident ? $resident->middle_name : '');
        $data['religion'] = DocumentText::clean($resident ? $resident->religion : '');
        $data['sex'] = $this->sex($resident);
        $data['citizenship'] = DocumentText::clean($resident ? $resident->citizenship : '');
        $data['age'] = DocumentText::clean($resident ? $resident->age : '');
        $data['birth_date'] = DocumentText::dateParts($resident ? $resident->birth_date : null);
        $data['birth_place'] = DocumentText::clean($resident ? $resident->birth_place : '');
        $data['civil_status'] = $this->civilStatus($resident);
        $data['spouse_name'] = $this->spouseName($resident, $household);
        $data['member_type'] = $this->memberType($household);
        // No attainment on the record prints blank: the sheet never invents one.
        $data['education'] = $resident && $resident->educational_attainment
            ? DocumentText::clean($resident->educational_attainment_label)
            : '';
        $data['occupation'] = DocumentText::clean($resident ? $resident->occupation : '');
        $data['mobile'] = DocumentText::clean($resident ? $resident->mobile_no : '');
        $data['previous_address'] = DocumentText::clean($household ? $household->prev_address : '');
        $data['owner_name'] = DocumentText::clean($household ? $this->headName($household) : '');

        $unit = DocumentText::blockLot(
            $property && $property->block !== null ? (int) $property->block : null,
            $property && $property->lot !== null ? (int) $property->lot : null
        );

        $data['block'] = $unit['block'];
        $data['lot'] = $unit['lot'];

        $data['members'] = $this->members($household, $resident);
        $data['member_rows'] = self::MEMBER_ROWS;
        $data['fee'] = $this->amount('membership_fee', (float) config('documents.membership.fee', 0));
        $data['dues'] = $this->amount('monthly_dues', (float) config('documents.membership.dues', 0));

        return $this->applySupplied($data, $params);
    }

    /**
     * The details supplied for this one form, where the record has none.
     *
     * A supplied value never overrides the record — the sheet is the resident's
     * own, and a detail typed for one print-out must not hide what the profile
     * actually says. The spouse is read last, because it depends on the civil
     * status ahead of it: a supplied "Married" carries its spouse with it.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function applySupplied(array $data, array $params): array
    {
        foreach (MembershipFormDetails::labels() as $key => $label) {
            if ($key === 'spouse_name' || ($data[$key] ?? '') !== '') {
                continue;
            }

            $given = DocumentText::clean($params[$key] ?? '');

            if ($given !== '') {
                $data[$key] = $given;
            }
        }

        if ($data['civil_status'] === 'Married' && $data['spouse_name'] === '') {
            $data['spouse_name'] = DocumentText::clean($params['spouse_name'] ?? '');
        }

        return $data;
    }

    /**
     * The form exactly as the paper prints it with nothing filled in.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [
            'date'             => DocumentText::dateParts(null),
            'surname'          => '',
            'first_name'       => '',
            'middle_name'      => '',
            'religion'         => '',
            'sex'              => '',
            'citizenship'      => '',
            'age'              => '',
            'birth_date'       => DocumentText::dateParts(null),
            'birth_place'      => '',
            'civil_status'     => '',
            'spouse_name'      => '',
            'member_type'      => '',
            'education'        => '',
            'occupation'       => '',
            'mobile'           => '',
            'previous_address' => '',
            'owner_name'       => '',
            'block'            => '',
            'lot'              => '',
            'members'          => $this->emptyRows(self::MEMBER_ROWS),
            'member_rows'      => self::MEMBER_ROWS,
            'fee'              => $this->amount('membership_fee', (float) config('documents.membership.fee', 0)),
            'dues'             => $this->amount('monthly_dues', (float) config('documents.membership.dues', 0)),
        ];
    }

    /**
     * An amount the association charges, from the collections settings or the
     * document configuration, printed the way the paper's NOTE prints it.
     */
    private function amount(string $key, float $default): string
    {
        $value = \App\Utility\SettingsHelper::get($key, 'collections');

        return number_format(is_numeric($value) ? (float) $value : $default, 2);
    }

    // =====================================================================
    // The member
    // =====================================================================

    /**
     * The sex box the record falls under: Male, Female, or none.
     */
    private function sex(?Resident $resident): string
    {
        $sex = $resident ? strtolower(DocumentText::clean($resident->sex)) : '';

        if ($sex === 'male' || $sex === 'm') {
            return 'Male';
        }

        return ($sex === 'female' || $sex === 'f') ? 'Female' : '';
    }

    /**
     * The civil status the paper offers for the record.
     */
    private function civilStatus(?Resident $resident): string
    {
        $status = $resident ? strtolower(DocumentText::clean($resident->civil_status)) : '';

        switch ($status) {
            case 'single':
                return 'Single';

            case 'married':
                return 'Married';

            case 'widowed':
            case 'widow':
                return 'Widow';

            case 'separated':
            case 'divorced':
                return 'Separated';
        }

        return '';
    }

    /**
     * The spouse, only for a married member: the name on record, or the member
     * of the same household marked as the spouse.
     */
    private function spouseName(?Resident $resident, ?Household $household): string
    {
        if (! $resident || $this->civilStatus($resident) !== 'Married') {
            return '';
        }

        $stored = DocumentText::clean($resident->spouse_name);

        if ($stored !== '') {
            return $stored;
        }

        foreach ($household ? $household->memberRecords : [] as $record) {
            $other = $record->resident;

            if (! $other || $other->id === $resident->id) {
                continue;
            }

            if (stripos((string) $record->relationship, 'spouse') !== false) {
                return DocumentText::person($other->surname, $other->first_name, $other->middle_name, $other->suffix);
            }
        }

        return '';
    }

    /**
     * How the unit is held, as the paper's four boxes read it.
     */
    private function memberType(?Household $household): string
    {
        $type = $household ? strtolower(DocumentText::clean($household->type)) : '';

        switch ($type) {
            case 'owner':
            case 'owned':
                return 'Owner';

            case 'tenant':
            case 'rented':
                return 'Renter';

            case 'caretaker':
                return 'Caretaker';

            case 'informal_settler':
            case 'informal settler':
                return 'Informal Settler';
        }

        return '';
    }

    /**
     * The unit's head, which is who the paper's "Name of owner" line prints.
     */
    private function headName(Household $household): string
    {
        $head = $household->head_resident;

        return $head
            ? DocumentText::person($head->surname, $head->first_name, $head->middle_name, $head->suffix)
            : '';
    }

    // =====================================================================
    // The household table
    // =====================================================================

    /**
     * Every member of the household except the one this form is about.
     *
     * @return array<int, array<string, string>>
     */
    private function members(?Household $household, ?Resident $subject): array
    {
        if (! $household) {
            return $this->emptyRows(self::MEMBER_ROWS);
        }

        $household->loadMissing('memberRecords.resident', 'headMember.resident');

        $rows = [];
        $seen = [];

        $head = $household->head_resident;

        if ($head) {
            $seen[] = $head->id;

            if (! $subject || $head->id !== $subject->id) {
                $rows[] = $this->memberRow($head, 'Head');
            }
        }

        foreach ($household->memberRecords as $record) {
            $member = $record->resident;

            if (! $member || in_array($member->id, $seen, true)) {
                continue;
            }

            $seen[] = $member->id;

            if ($subject && $member->id === $subject->id) {
                continue;
            }

            $rows[] = $this->memberRow($member, ucfirst(DocumentText::clean($record->relationship)));
        }

        return $this->pad($rows);
    }

    /**
     * One row of the household table.
     *
     * @return array<string, string>
     */
    private function memberRow(Resident $resident, string $relation): array
    {
        return [
            'name'       => DocumentText::person($resident->surname, $resident->first_name, $resident->middle_name, $resident->suffix),
            'age'        => DocumentText::clean($resident->age),
            'birth_date' => DocumentText::dateParts($resident->birth_date)['long'],
            'birth_place' => DocumentText::clean($resident->birth_place),
            'relation'   => $relation,
            'occupation' => DocumentText::clean($resident->occupation),
        ];
    }

    /**
     * The rows the paper carries, extended by whole sheets when the household
     * is larger than one sheet holds.
     *
     * @param  array<int, array<string, string>>  $rows
     * @return array<int, array<string, string>>
     */
    private function pad(array $rows): array
    {
        $sheets = max(1, (int) ceil(count($rows) / self::MEMBER_ROWS));
        $total = $sheets * self::MEMBER_ROWS;

        return array_merge($rows, $this->emptyRows(max(0, $total - count($rows))));
    }

    /**
     * Blank rows of the household table.
     *
     * @return array<int, array<string, string>>
     */
    private function emptyRows(int $count): array
    {
        return array_fill(0, $count, [
            'name'        => '',
            'age'         => '',
            'birth_date'  => '',
            'birth_place' => '',
            'relation'    => '',
            'occupation'  => '',
        ]);
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
     * The details supplied for the request a form is printed from.
     *
     * They are read from the request's own record rather than from the page's
     * parameters, so a detail the homeowner supplied — a contact number above
     * all — never has to travel in a print URL. A value the office typed on the
     * print page is for that print, so it wins over the stored one.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function withSuppliedForRequest(array $params): array
    {
        $id = $params['requestId'] ?? null;

        if (! is_numeric($id)) {
            return $params;
        }

        $request = \App\Models\Request::find((int) $id);
        $supplied = $request && is_array($request->membership_data) ? $request->membership_data : [];

        return $params + $supplied;
    }

    /**
     * The member the form is about, with the household it belongs to loaded.
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
     * The household a member belongs to, by their membership row or through
     * the portal account that holds the unit.
     */
    private function householdOf(?Resident $resident): ?Household
    {
        if (! $resident) {
            return null;
        }

        $household = $resident->household ?: ($resident->user ? $resident->user->household : null);

        if ($household) {
            $household->loadMissing('memberRecords.resident', 'property');
        }

        return $household;
    }

}
