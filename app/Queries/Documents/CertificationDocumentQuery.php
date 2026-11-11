<?php

declare(strict_types=1);

namespace App\Queries\Documents;

use App\Models\Resident;
use App\Utility\DocumentText;
use Illuminate\Database\Eloquent\Model;

/**
 * The Certification — the association certifying who occupies a unit, and for
 * what purpose the certificate is being asked.
 *
 * The certificate is the framed family's shortest sheet: it names the unit, the
 * occupant and the purpose ticked, and it carries a control and an O.R. number
 * the office writes on it. Everything else on the paper is the association's
 * fixed wording.
 *
 * The purpose list is the paper's own and is read down the first column and
 * then the second, so the order below is the printed order rather than the
 * enum's; both name the same nine purposes.
 */
class CertificationDocumentQuery
{
    /** The data the paper fills in, blank until the office supplies it. */
    const PAPER_FIELDS = ['block', 'lot', 'occupant_name', 'purposes', 'issued_date'];

    /**
     * The purposes, in the order the paper's two columns read them.
     *
     * Nine options over two columns: the first five stand in the left column
     * (Residency, Employment, Burial Assistance, Indigency, Others) and the
     * remaining four in the right. Filling down the left column and then the
     * right is what puts each option where the paper prints it.
     *
     * @var array<string, string>
     */
    const PURPOSES = [
        'residency'              => 'Residency',
        'employment'             => 'Employment',
        'burial_assistance'      => 'Burial Assistance',
        'indigency'              => 'Indigency',
        'others'                 => 'Others',
        'financial_assistance'   => 'Financial Assistance',
        'educational_assistance' => 'Educational Assistance',
        'loan'                   => 'Loan',
        'moved_out'              => 'Moved out',
    ];

    /** The purpose whose own wording the office writes on the paper. */
    const OTHER = 'others';

    /** The blank that follows "Others", in mm. */
    const OTHER_BLANK = 30;

    /** How the certificate addresses the occupant. */
    const SALUTATIONS = ['MR.', 'MS.', 'MRS.'];

    /** What follows the unit on every certificate. */
    const ADDRESS_TAIL = 'Tradition Homes Phase 2 Barangay Inocencio, Trece Martires City, Cavite';

    /**
     * The view data for one certificate.
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

        $data['block'] = $unit['block'];
        $data['lot'] = $unit['lot'];
        $data['occupant_name'] = $this->name($resident);
        $data['salutation'] = $this->salutation($resident);
        $data['purposes'] = $this->purposes(
            DocumentText::clean($params['purpose'] ?? ''),
            DocumentText::clean($params['purposeOther'] ?? '')
        );
        $data['issued_date'] = DocumentText::dateParts($params['issuedAt'] ?? now());
        $data['control_no'] = DocumentText::clean($params['controlNumber'] ?? '');
        $data['or_no'] = DocumentText::clean($params['orNumber'] ?? '');

        return $data;
    }

    /**
     * The certificate exactly as the paper prints it with nothing filled in.
     *
     * @return array<string, mixed>
     */
    public function blank(): array
    {
        return [
            'block'         => '',
            'lot'           => '',
            'occupant_name' => '',
            'salutation'    => '',
            'purposes'      => $this->purposes('', ''),
            'issued_date'   => DocumentText::dateParts(null),
            'control_no'    => '',
            'or_no'         => '',
        ];
    }

    // =====================================================================
    // Values
    // =====================================================================

    /**
     * The purposes as the tick list prints them.
     *
     * Every option always answers: the label, whether it is the one chosen, and
     * — for "Others" — the blank the office writes on. A certificate with no
     * purpose still prints all nine boxes, because that is what the paper is.
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
     * How the certificate addresses the occupant, as the paper's three choices
     * read: a married woman is Mrs., any other woman Ms., a man Mr.
     */
    private function salutation(?Resident $resident): string
    {
        if (! $resident) {
            return '';
        }

        $female = strtolower(DocumentText::clean($resident->sex)) === 'female';
        $married = strtolower(DocumentText::clean($resident->civil_status)) === 'married';

        if ($female) {
            return $married ? 'MRS.' : 'MS.';
        }

        return DocumentText::clean($resident->sex) === '' ? '' : 'MR.';
    }

    /**
     * The occupant's name, as the certificate writes it.
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
     * The occupant, with the unit loaded.
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
     * The unit the occupant lives in.
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
