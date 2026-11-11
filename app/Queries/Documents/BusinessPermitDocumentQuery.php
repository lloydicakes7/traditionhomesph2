<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Business Permit — the association certifying that a named homeowner may
 * run a home-based business, or have a utility connected, at their unit.
 *
 * The paper asks for the applicant's own details at the top, the purpose ticked
 * in the middle and the date at the foot; the rest is the association's fixed
 * wording. A permit's purpose list is the paper's own, filled down the first
 * column and then the second, and "Other" carries a blank for the office to
 * write on.
 */
class BusinessPermitDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = [
        'applicant_name',
        'gender',
        'block',
        'lot',
        'birth_date',
        'age',
        'mobile_no',
        'purposes',
        'issued_date',
    ];

    /**
     * The purposes, in the order the paper's two columns read them.
     *
     * Six options over two columns: Business Permit, Franchising and Internet
     * Connection stand in the left column; Electrical Connection, Water
     * Connection and Other in the right.
     *
     * @var array<string, string>
     */
    const PURPOSES = [
        'business_permit'       => 'Business Permit',
        'franchising'           => 'Franchising',
        'internet_connection'   => 'Internet Connection',
        'electrical_connection' => 'Electrical Connection',
        'water_connection'      => 'Water Connection',
        'other'                 => 'Other',
    ];

    /** The purpose whose own wording the office writes on the paper. */
    const OTHER = 'other';

    /** The blank that follows "Other", in mm. */
    const OTHER_BLANK = 35;

    /** What follows the unit in the address line. */
    const ADDRESS_TAIL = 'Phase 2 Tradition Homes, Barangay Inocencio, Trece Martires City, Cavite';

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
        $unit = DocumentText::blockLot(
            $property && $property->block !== null ? (int) $property->block : null,
            $property && $property->lot !== null ? (int) $property->lot : null
        );

        $data = $this->blank();

        $data['applicant_name'] = $this->name($resident);
        $data['gender'] = DocumentText::clean($resident ? $resident->sex : '');
        $data['gender'] = $data['gender'] === '' ? '' : ucfirst($data['gender']);
        $data['block'] = $unit['block'];
        $data['lot'] = $unit['lot'];
        $data['birth_date'] = DocumentText::dateParts($resident ? $resident->birth_date : null);
        $data['age'] = DocumentText::clean($resident ? $resident->age : '');
        $data['mobile_no'] = DocumentText::clean($resident ? $resident->mobile_no : '');
        $data['purposes'] = $this->purposes(
            DocumentText::clean($params['purpose'] ?? ''),
            DocumentText::clean($params['purposeOther'] ?? '')
        );
        $data['issued_date'] = DocumentText::dateParts($params['issuedAt'] ?? now());
        $data['control_no'] = DocumentText::clean($params['controlNumber'] ?? '');

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
            'applicant_name' => '',
            'gender'         => '',
            'block'          => '',
            'lot'            => '',
            'birth_date'     => DocumentText::dateParts(null),
            'age'            => '',
            'mobile_no'      => '',
            'purposes'       => $this->purposes('', ''),
            'issued_date'    => DocumentText::dateParts(null),
            'control_no'     => '',
        ];
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * The purposes as the tick list prints them: every option, whether it is
     * the one chosen, and the blank "Other" carries.
     *
     * @return array<int, array{label: string, checked: bool, blank: int|null, value: string}>
     */
    private function purposes(string $selected, string $other): array
    {
        $options = [];

        foreach (self::PURPOSES as $key => $label) {
            $isOther = $key === self::OTHER;

            $options[] = [
                'label'   => $label,
                'checked' => $selected !== '' && (strcasecmp($key, $selected) === 0 || strcasecmp($label, $selected) === 0),
                'blank'   => $isOther ? self::OTHER_BLANK : null,
                'value'   => $isOther ? $other : '',
            ];
        }

        return $options;
    }

    /**
     * The applicant's name, as the permit writes it.
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
     * The applicant, with the unit loaded.
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
     * The unit the applicant lives in.
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
