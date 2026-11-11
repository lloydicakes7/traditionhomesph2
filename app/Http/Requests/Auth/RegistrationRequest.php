<?php

namespace App\Http\Requests\Auth;

use App\Enums\EducationalAttainment;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Rules\EmailAvailable;
use App\Rules\PasswordPolicy;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The unified registration wizard's rules.
 *
 * The personal fields are the resident form's own field names and choices,
 * taken from ResidentRequest; registration only tightens the few the office
 * leaves open (a contact number and a birth place are required of an applicant)
 * and adds the account, address, ID and consent fields the wizard collects.
 */
class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Every field of the five steps, keyed by the wizard's own properties.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = (new ResidentRequest)->rules();

        $rules['form.surname']    = ['required', 'string', 'max:255', 'regex:/^[^\d]+$/u'];
        $rules['form.first_name'] = ['required', 'string', 'max:255', 'regex:/^[^\d]+$/u'];
        $rules['form.middle_name'] = ['nullable', 'string', 'max:255', 'regex:/^[^\d]*$/u'];
        $rules['form.suffix']      = ['nullable', 'string', 'max:50', 'regex:/^[^\d]*$/u'];

        $rules['form.birth_date']  = ['required', 'date', 'before:today', 'after:1900-01-01'];
        $rules['form.birth_place'] = ['required', 'string', 'max:255'];

        $rules['form.mobile_no'] = ['required', 'string', 'max:20', new PhilippineMobileNumber];

        $rules['form.spouse_name'] = ['nullable', 'required_if:form.civil_status,married', 'string', 'max:255'];

        $rules['form.educational_attainment'] = ['nullable', Rule::in(EducationalAttainment::values())];

        return $rules + $this->registrationRules();
    }

    /**
     * The account, unit, household, ID and consent fields.
     *
     * @return array<string, array<int, mixed>>
     */
    private function registrationRules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:255', new EmailAvailable],
            'password' => ['required', 'string', 'confirmed', new PasswordPolicy],

            'property.block' => ['required', 'integer', 'min:1', 'max:9999'],
            'property.lot'   => ['required', 'integer', 'min:1', 'max:9999'],
            'property.sqm'   => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'property.type'  => ['nullable', Rule::in(PropertyType::values())],

            'household.type'           => ['nullable', Rule::in(HouseholdType::values())],
            'household.prev_address'   => ['nullable', 'string', 'max:500'],

            'link_household' => ['nullable', 'boolean'],

            'idFile' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],

            'consent.terms'   => ['accepted'],
            'consent.privacy' => ['accepted'],
        ];
    }
}
