<?php

namespace App\Http\Requests\Homeowners;

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\SectorType;
use App\Enums\Sex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The same rules under another form property.
     *
     * The dialogs that collect a resident each hold the fields in their own
     * public array, so they ask for the rules by prefix instead of copying the
     * list. `form` is the plain request itself.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(string $prefix): array
    {
        $rules = (new static)->rules();

        if ($prefix === 'form') {
            return $rules;
        }

        $prefixed = [];

        foreach ($rules as $field => $rule) {
            $prefixed[preg_replace('/^form\./', $prefix . '.', $field)] = $rule;
        }

        return $prefixed;
    }

    public function rules(): array
    {
        return [
            'form.surname'                => ['required', 'string', 'max:255'],
            'form.first_name'             => ['required', 'string', 'max:255'],
            'form.middle_name'            => ['nullable', 'string', 'max:255'],
            'form.suffix'                 => ['nullable', 'string', 'max:50'],
            'form.mobile_no'              => ['nullable', 'string', 'max:50'],
            'form.sex'                    => ['required', Rule::in(Sex::values())],
            'form.birth_date'             => ['required', 'date'],
            'form.birth_place'            => ['nullable', 'string', 'max:255'],
            'form.religion'               => ['nullable', 'string', 'max:255'],
            'form.citizenship'            => ['required', 'string', 'max:255'],
            'form.civil_status'           => ['required', Rule::in(CivilStatus::values())],
            'form.spouse_name'            => ['nullable', 'string', 'max:255'],
            'form.occupation'             => ['nullable', 'string', 'max:255'],
            'form.educational_attainment' => ['nullable', Rule::in(EducationalAttainment::values())],

            'form.philsys_card_no'        => ['nullable', 'string', 'regex:/^(\d{16}|\d{4}-\d{4}-\d{4}-\d{4})$/'],

            'form.sectors'                => ['nullable', 'array'],
            'form.sectors.*'              => [Rule::in(SectorType::values())],
        ];
    }
}
