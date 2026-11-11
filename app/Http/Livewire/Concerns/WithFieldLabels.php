<?php

namespace App\Http\Livewire\Concerns;

/**
 * The names validation messages read out for a component's fields.
 *
 * A form held in an array is validated under dotted keys, so an unnamed field
 * answers "The form.user_id field is required." Every component names its
 * fields for the reader from the rules it is checking, and a component that
 * words one its own way keeps its own validationAttributes() — those win.
 */
trait WithFieldLabels
{
    /**
     * The last segment of a key whose column name alone would confuse.
     */
    private static $fieldLabels = [
        'account_id'               => 'account',
        'address_id'               => 'address',
        'amenity_id'               => 'facility',
        'amenityImage'             => 'facility photo',
        'announcementImage'        => 'announcement image',
        'birth_date'               => 'birth date',
        'birth_place'              => 'birth place',
        'contentImage'             => 'page image',
        'expense_category_id'      => 'category',
        'footerText'               => 'letterhead footer',
        'from_account_id'          => 'source account',
        'headerHtml'               => 'letterhead header',
        'hoa_name'                 => 'HOA name',
        'importFile'               => 'import file',
        'is_active'                => 'active',
        'is_bookable'              => 'bookable on the portal',
        'is_cash'                  => 'cash',
        'is_head'                  => 'household head',
        'is_primary'               => 'primary',
        'membershipChecker1'       => 'membership requirement',
        'membershipChecker2'       => 'membership requirement',
        'memberPhoto'              => 'member photo',
        'mobile_no'                => 'mobile number',
        'offset_account_id'        => 'offset account',
        'payment_method_id'        => 'payment method',
        'prev_address'             => 'previous address',
        'product_category_id'      => 'category',
        'profile_photo'            => 'profile photo',
        'profilePhoto'             => 'profile photo',
        'property_id'              => 'property',
        'qr'                       => 'QR code',
        'reference_no'             => 'reference number',
        'resident_id'              => 'resident',
        'role_id'                  => 'role',
        'sqm'                      => 'floor area',
        'supplier_id'              => 'supplier',
        'to_account_id'            => 'destination account',
        'user_id'                  => 'homeowner',
    ];

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        $rules = $rules ?? $this->getRules();

        return parent::validate(
            $rules,
            $messages,
            $attributes ?: $this->getValidationAttributes() + $this->fieldLabels($rules)
        );
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, string>
     */
    private function fieldLabels(array $rules): array
    {
        $labels = [];

        foreach (array_keys($rules) as $field) {
            $labels[$field] = self::labelFor((string) $field);
        }

        return $labels;
    }

    private static function labelFor(string $field): string
    {
        $segments = array_filter(explode('.', $field), function (string $segment): bool {
            return $segment !== '' && $segment !== '*';
        });

        $name = (string) end($segments);

        return self::$fieldLabels[$name] ?? strtolower(
            (string) preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', ' ', str_replace('_', ' ', $name))
        );
    }
}
