<?php

namespace App\Actions\Support\Requests\Concerns;

use App\Enums\BusinessPermitPurpose;
use App\Enums\RequestType;
use App\Models\Resident;
use App\Utility\MembershipFormDetails;
use Illuminate\Validation\ValidationException;

trait RequestPayload
{
    // ===== Payload =====

    /**
     * Map the validated form payload onto the request's columns.
     *
     * Only the fields the selected type carries are kept; every other field is
     * cleared, so switching a request's type never leaves stale values behind.
     */
    protected function payload(array $data): array
    {
        $type = $data['type'] ?? null;

        $payload = [
            'type'               => $type,
            'purpose'            => null,
            'business_name'      => null,
            'nature_of_business' => null,
            'hours_of_operation' => null,
            'purpose_other'      => null,
            'permit_type'        => null,
            'location'           => null,
            'scope_of_work'      => null,
            'addressee'          => null,
            'letter_date'        => null,
            'membership_data'    => null,
        ];

        switch ($type) {
            case RequestType::MEMBERSHIP_FORM:
                $payload['membership_data'] = $this->membershipDetails($data['membership'] ?? []);
                break;

            case RequestType::CERTIFICATION:
                $payload['purpose'] = $this->nullIfBlank($data['purpose'] ?? null);
                break;

            case RequestType::BUSINESS_PERMIT:
                $payload['business_name'] = $this->nullIfBlank($data['business_name'] ?? null);
                $payload['nature_of_business'] = $this->nullIfBlank($data['nature_of_business'] ?? null);
                $payload['hours_of_operation'] = $this->nullIfBlank($data['hours_of_operation'] ?? null);
                $payload['purpose'] = $this->nullIfBlank($data['purpose'] ?? null);

                if ($payload['purpose'] === BusinessPermitPurpose::OTHER) {
                    $payload['purpose_other'] = $this->nullIfBlank($data['purpose_other'] ?? null);
                }
                break;

            case RequestType::HOA_PERMIT:
                $payload['permit_type'] = $this->nullIfBlank($data['permit_type'] ?? null);
                $payload['location'] = $this->nullIfBlank($data['location'] ?? null);
                $payload['scope_of_work'] = $this->nullIfBlank($data['scope_of_work'] ?? null);
                break;

            case RequestType::ENDORSEMENT_LETTER:
                $payload['addressee'] = $this->nullIfBlank($data['addressee'] ?? null);
                $payload['letter_date'] = $this->nullIfBlank($data['letter_date'] ?? null);
                break;
        }

        return $payload;
    }

    // ===== Membership Form =====

    /**
     * Refuse a Membership Form that is still missing what the paper prints.
     *
     * The form is printed from the profile, the household and the unit; a detail
     * those records leave blank may be supplied for this one form (see
     * \App\Utility\MembershipFormDetails), and only what is blank after that is
     * missing. Block and lot are never supplied — they identify the unit, so the
     * office completes them on the household's own records.
     */
    protected function ensureMembershipProfileReady(array $data, ?int $residentId): void
    {
        if (($data['type'] ?? null) !== RequestType::MEMBERSHIP_FORM) {
            return;
        }

        $supplied = MembershipFormDetails::columns(
            array_keys($this->membershipDetails($data['membership'] ?? []) ?? [])
        );

        $resident = $residentId ? Resident::find($residentId) : null;
        $missing = $resident
            ? $resident->membershipFormMissing($supplied)
            : Resident::MEMBERSHIP_FORM_REQUIRED;

        if ($missing === []) {
            return;
        }

        throw ValidationException::withMessages([
            'form.type' => 'The Membership Form cannot be filed yet — it still needs: '
                . implode(', ', array_values($missing))
                . '. Fill these in on the form, or ask the HOA office to complete them on the records.',
        ]);
    }

    /**
     * The Membership Form details supplied for this request, known keys only.
     *
     * They belong to the request, never to the resident's profile: the printed
     * sheet prefers the record whenever the record already has a value.
     *
     * @param  mixed  $values
     * @return array<string, string>|null
     */
    private function membershipDetails($values): ?array
    {
        $details = MembershipFormDetails::clean((array) $values);

        return $details === [] ? null : $details;
    }

    // ===== Helpers =====

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
