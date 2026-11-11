<?php

namespace App\Http\Livewire\Concerns;

use App\Enums\BusinessPermitPurpose;
use App\Enums\CertificationPurpose;
use App\Enums\RequestType;
use App\Utility\MembershipFormDetails;
use Illuminate\Validation\Rule;

trait WithRequestFilingRules
{
    // ===== Rules =====

    /**
     * What filing a request needs — built from the type the form selected.
     *
     * Only the fields the chosen type carries are returned, so the form
     * validates what it actually shows. A Membership Form is printed from the
     * homeowner's profile record, so it carries no required fields of its own;
     * the optional details it does allow are the ones a record may leave blank,
     * and the filing Action checks what is still missing before it writes.
     *
     * @param  array|null  $form  The form being validated (its type and purpose
     *                            decide which rules apply)
     * @return array<string, array>
     */
    protected function filingRules(?array $form = null): array
    {
        $form = $form ?? [];
        $type = $form['type'] ?? null;

        $rules = [
            'form.type' => ['required', Rule::in(RequestType::values())],
        ];

        if ($type === RequestType::MEMBERSHIP_FORM) {
            $rules['form.membership'] = ['nullable', 'array'];

            foreach (MembershipFormDetails::FIELDS as $key => $field) {
                $rules['form.membership.' . $key] = ['nullable', 'string', 'max:' . $field['max']];
            }
        }

        if ($type === RequestType::CERTIFICATION) {
            $rules['form.purpose'] = ['required', Rule::in(CertificationPurpose::values())];
        }

        if ($type === RequestType::BUSINESS_PERMIT) {
            $rules['form.business_name'] = ['required', 'string', 'max:255'];
            $rules['form.nature_of_business'] = ['required', 'string', 'max:255'];
            $rules['form.hours_of_operation'] = ['required', 'string', 'max:255'];
            $rules['form.purpose'] = ['required', Rule::in(BusinessPermitPurpose::values())];
            $rules['form.purpose_other'] = [
                ($form['purpose'] ?? null) === BusinessPermitPurpose::OTHER ? 'required' : 'nullable',
                'string',
                'max:255',
            ];
        }

        if ($type === RequestType::HOA_PERMIT) {
            $rules['form.permit_type'] = ['required', 'string', 'max:255'];
            $rules['form.location'] = ['required', 'string', 'max:200'];
            $rules['form.scope_of_work'] = ['required', 'string', 'max:2000'];
        }

        if ($type === RequestType::ENDORSEMENT_LETTER) {
            $rules['form.addressee'] = ['required', 'string', 'max:255'];
            $rules['form.letter_date'] = ['required', 'date'];
        }

        return $rules;
    }

    /**
     * The same fields, plus the resident the office files the request under.
     *
     * @return array<string, array>
     */
    protected function filingForResidentRules(?array $form = null): array
    {
        return array_merge($this->filingRules($form), [
            'form.resident_id' => ['required', 'integer', 'exists:residents,id'],
        ]);
    }
}
