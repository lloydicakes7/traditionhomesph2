<?php

namespace App\Queries\Support\Requests;

use App\Models\Resident;
use App\Queries\Documents\MembershipFormDocumentQuery;
use App\Utility\MembershipFormDetails;

/**
 * What a request form shows from the resident's own records.
 *
 * A Membership Form is never typed in: the paper is printed from the resident's
 * profile and the household it belongs to. The filing form therefore shows that
 * same record back to the homeowner — the details, the unit, the household
 * members and the owner — and names anything the office still has to complete,
 * so a form that cannot be printed yet is visible before it is filed rather
 * than after it is approved.
 *
 * The values are the printed form's own (`MembershipFormDocumentQuery`), so the
 * request form and the sheet of paper can never disagree about what the record
 * says. The Endorsement Letter's respondent address is read from the same
 * household the requester belongs to.
 */
class RequestProfileQuery
{
    /** @var \App\Queries\Documents\MembershipFormDocumentQuery */
    protected $document;

    public function __construct(MembershipFormDocumentQuery $document)
    {
        $this->document = $document;
    }

    // ===== Queries =====

    /**
     * The Membership Form as the resident's own record fills it in.
     *
     * `ready` is whether the form can be filed at all; `missing` names the
     * blanks the office still has to complete; `profile` is the form's own
     * view data, or null while there is no resident on file.
     *
     * @return array<string, mixed>
     */
    public function membershipForm(?int $residentId): array
    {
        $resident = $this->resident($residentId);

        if (! $resident) {
            return [
                'ready'          => false,
                'missing'        => ['a resident record'],
                'missing_fields' => [],
                'profile'        => null,
            ];
        }

        $missing = $resident->membershipFormMissing();
        $profile = $this->document->preview(['residentId' => $resident->id]);

        // What the sheet would print blank, and may therefore be supplied for
        // this one form. The spouse only counts for a married member: the paper
        // prints no spouse line for anyone else.
        $suppliable = [];

        foreach (MembershipFormDetails::labels() as $key => $label) {
            if ($key === 'spouse_name' && ($profile['civil_status'] ?? '') !== 'Married') {
                continue;
            }

            if (($profile[$key] ?? '') === '') {
                $suppliable[$key] = $label;
            }
        }

        return [
            'ready'          => $missing === [],
            'missing'        => array_values($missing),
            'missing_fields' => $suppliable,
            'profile'        => $profile,
        ];
    }

    /**
     * The unit the resident's household holds.
     *
     * @return array<string, string>
     */
    public function unit(?int $residentId): array
    {
        $resident = $this->resident($residentId);
        $property = $resident ? $resident->unit() : null;

        return [
            'block' => $property && $property->block !== null ? (string) $property->block : '',
            'lot'   => $property && $property->lot !== null ? (string) $property->lot : '',
        ];
    }

    // ===== Helpers =====

    /**
     * The resident the form is about, with the household and unit loaded.
     */
    private function resident(?int $residentId): ?Resident
    {
        if (! $residentId) {
            return null;
        }

        return Resident::query()
            ->with(['household.property', 'user.household.property'])
            ->find($residentId);
    }
}
