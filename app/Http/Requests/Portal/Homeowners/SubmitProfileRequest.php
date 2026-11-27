<?php

namespace App\Http\Requests\Portal\Homeowners;

use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Models\Property;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The onboarding form's rules: the person in full, the unit they live in, and
 * the document the office checks.
 *
 * The personal fields are the resident form's own list, with the few the office
 * asks of every household head tightened to required. The ID is optional here
 * because a resident correcting a refused profile already has one on file; the
 * wizard refuses a submission that has neither.
 */
class SubmitProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ===== Rules =====

    protected function prepareForValidation(): void
    {
        $this->merge([
            'property' => Property::normalizeUnit((array) $this->input('property', [])),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = ResidentRequest::rulesFor('form');

        $rules['form.birth_date']  = ['required', 'date', 'before:today', 'after:1900-01-01'];
        $rules['form.birth_place'] = ['required', 'string', 'max:255'];
        $rules['form.citizenship'] = ['required', 'string', 'max:255'];
        $rules['form.mobile_no']   = ['required', 'string', 'max:11', new PhilippineMobileNumber];
        $rules['form.spouse_name'] = ['nullable', 'required_if:form.civil_status,married', 'string', 'max:255'];

        return $rules + [
            'property.block' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'property.lot'   => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'property.sqm'   => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'property.type'  => ['nullable', Rule::in(PropertyType::values())],

            'household.type'         => ['required', Rule::in(HouseholdType::values())],
            'household.prev_address' => ['nullable', 'string', 'max:500'],
            'household.relationship' => ['nullable', 'string', 'max:255'],
            'household.be_head'      => ['boolean'],

            'idFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}
