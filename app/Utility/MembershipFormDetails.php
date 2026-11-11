<?php

namespace App\Utility;

/**
 * The Membership Form details a record may leave blank.
 *
 * The form is printed from the resident's own record, so a detail the record
 * does not carry would print as an empty line. These are the details that may
 * be supplied for a single form instead — on the homeowner's request or on the
 * office's print page — and the one list both sides, the validation and the
 * printed sheet read, so a detail cannot be asked for in one place and ignored
 * in another.
 *
 * A supplied value is request-specific: it is kept on the request's own
 * `membership_data` (or in the print form's parameters), never written back to
 * the resident's profile, and the printed sheet always prefers the record when
 * the record already has a value.
 *
 * The utility holds plain data only: no model and no enum, so every layer that
 * needs the list can read it.
 */
class MembershipFormDetails
{
    /**
     * The suppliable details.
     *
     * `column` is the resident column the detail is read from, which is also
     * how a supplied value is matched against what the record is missing;
     * `previous_address` is the household's, so it names no resident column.
     */
    const FIELDS = [
        'religion'         => ['label' => 'Religion', 'column' => 'religion', 'max' => 120],
        'sex'              => ['label' => 'Sex', 'column' => 'sex', 'max' => 10],
        'citizenship'      => ['label' => 'Citizenship', 'column' => 'citizenship', 'max' => 120],
        'birth_place'      => ['label' => 'Birthplace', 'column' => 'birth_place', 'max' => 255],
        'civil_status'     => ['label' => 'Civil Status', 'column' => 'civil_status', 'max' => 20],
        'spouse_name'      => ['label' => 'Spouse Name', 'column' => 'spouse_name', 'max' => 255],
        'education'        => ['label' => 'Highest Educational Attainment', 'column' => 'educational_attainment', 'max' => 60],
        'occupation'       => ['label' => 'Occupation', 'column' => 'occupation', 'max' => 255],
        'mobile'           => ['label' => 'Mobile Phone Number', 'column' => 'mobile_no', 'max' => 30],
        'previous_address' => ['label' => 'Previous Address', 'column' => null, 'max' => 255],
    ];

    /**
     * The details the office may type on the print page.
     *
     * A print page's own values reach the sheet through the document's URL, so
     * the detail the association treats as private — a contact number — is not
     * among them: it is supplied by the homeowner on the request, or completed
     * on the resident's record, and read from there.
     */
    const PRINT_PAGE = [
        'previous_address',
    ];

    /**
     * The form's keys, as the form writes them.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return array_map(function (array $field): string {
            return $field['label'];
        }, self::FIELDS);
    }

    /**
     * The details the print page offers, as form key => label.
     *
     * @return array<string, string>
     */
    public static function printable(): array
    {
        return array_intersect_key(self::labels(), array_flip(self::PRINT_PAGE));
    }

    /**
     * The supplied values that may be kept: known keys, trimmed, blanks dropped.
     *
     * @param  mixed  $values
     * @return array<string, string>
     */
    public static function clean($values): array
    {
        $clean = [];

        foreach (self::FIELDS as $key => $field) {
            $value = trim((string) ($values[$key] ?? ''));

            if ($value !== '') {
                $clean[$key] = mb_substr($value, 0, (int) $field['max']);
            }
        }

        return $clean;
    }

    /**
     * The resident columns a set of supplied values covers, so a record missing
     * one of them is not also counted as missing it on the form.
     *
     * @param  array<int, string>  $keys
     * @return array<int, string>
     */
    public static function columns(array $keys): array
    {
        $columns = [];

        foreach ($keys as $key) {
            $column = self::FIELDS[$key]['column'] ?? null;

            if ($column !== null) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
