<?php

namespace App\Utility;

use Illuminate\Support\Str;

class PermissionHelper
{
    /** @var array<string, array<int, string>>|null The flattened catalogue, read once */
    private static $modules;

    /** @var array<int, string>|null Every key in the catalogue, read once */
    private static $keys;

    /** @var array<string, bool>|null The same keys flipped, for an O(1) isValid() */
    private static $keyIndex;

    /**
     * The catalogue — group => module => the actions that module allows.
     *
     * An action is only listed where a surface actually asks for it: a module
     * the panel can only read offers View alone, and nothing offers an action
     * no Policy checks.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    const GROUPS = [
        'Homeowners' => [
            'Addresses'  => ['View', 'Create', 'Edit', 'Delete'],
            'Properties' => ['View', 'Create', 'Edit', 'Delete', 'Export', 'Import'],
            'Homeowners' => ['View', 'Create', 'Edit', 'Delete', 'Export', 'Import'],
            'Households' => ['View', 'Create', 'Edit', 'Delete', 'Export', 'Import'],
            'Residents'  => ['View', 'Create', 'Edit', 'Delete', 'Export', 'Import'],
        ],

        'HOA Center' => [
            'Directory'     => ['View', 'Create', 'Edit', 'Delete'],
            'Members'       => ['View', 'Create', 'Edit', 'Delete'],
            'Announcements' => ['View', 'Create', 'Edit', 'Delete'],
            'FAQs'          => ['View', 'Create', 'Edit', 'Delete'],
            'Contents'      => ['View', 'Create', 'Edit', 'Delete'],
        ],

        'Amenities Booking' => [
            'Amenities'        => ['View', 'Create', 'Edit', 'Delete'],
            'Amenity Bookings' => ['View', 'Create', 'Edit', 'Delete'],
        ],

        'Collections' => [
            'Invoices'               => ['View', 'Create', 'Edit', 'Delete', 'Export'],
            'Monthly Dues'           => ['View', 'Create', 'Edit', 'Delete', 'Export'],
            'Payments'               => ['View', 'Create', 'Edit', 'Delete', 'Export'],
            'Advance Deposits'       => ['View', 'Create', 'Edit', 'Delete'],
            'Products and Services'  => ['View', 'Create', 'Edit', 'Delete'],
            'Product Categories'     => ['View', 'Create', 'Edit', 'Delete'],
            'Email SOA'              => ['View', 'Create', 'Delete', 'Export'],
        ],

        'Expenses' => [
            'Expenses'           => ['View', 'Create', 'Edit', 'Approve', 'Delete', 'Export'],
            'Expense Categories' => ['View', 'Create', 'Edit', 'Delete'],
            'Suppliers'          => ['View', 'Create', 'Edit', 'Delete'],
        ],

        'Accounting' => [
            'Chart of Accounts'   => ['View', 'Create', 'Edit', 'Delete', 'Export'],
            'Journal Entries'     => ['View', 'Create', 'Edit', 'Delete', 'Export'],
            'Transfers'           => ['View', 'Create', 'Delete', 'Export'],
            'Transactions'        => ['View', 'Export'],
            'Period Close'        => ['View', 'Close', 'Reopen'],
        ],

        'Reports' => [
            'Reports' => ['View', 'Export'],
        ],

        'Printing' => [
            'Print Documents' => ['View', 'Print'],
            'Print Settings'  => ['View', 'Update'],
            'RBI Forms'       => ['View', 'Print'],
            'RBI Reports'     => ['View', 'Print'],
        ],

        'Support' => [
            'Requests'   => ['View', 'Create', 'Edit', 'Delete'],
            'Violations' => ['View', 'Create', 'Edit', 'Delete'],
            'Complaints' => ['View', 'Create', 'Edit', 'Delete'],
        ],
    ];

    /**
     * What each action lets an officer do. An action means the same thing on
     * every module it appears on, so the wording lives here once and the roles
     * form shows it beside each switch.
     *
     * @var array<string, string>
     */
    const NOTES = [
        'View'     => 'Read all of its information',
        'Create'   => 'Add new records',
        'Edit'     => 'Change records already on file',
        'Delete'   => 'Remove records',
        'Export'   => 'Download the list',
        'Import'   => 'Upload records in bulk',
        'Approve'  => 'Sign off on what others raised',
        'Complete' => 'Close a record as done',
        'Close'    => 'Lock the period',
        'Reopen'   => 'Unlock a period that was closed',
        'Print'    => 'Print and download the document',
        'Update'   => 'Change these settings',
    ];

    /**
     * The catalogue, grouped as the panel's menus group it.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    public static function groups(): array
    {
        return self::GROUPS;
    }

    /**
     * The group titles, in catalogue order.
     *
     * @return array<int, string>
     */
    public static function groupNames(): array
    {
        return array_keys(self::GROUPS);
    }

    /**
     * Every module in the catalogue, module => the actions it allows.
     *
     * @return array<string, array<int, string>>
     */
    public static function modules(): array
    {
        if (self::$modules === null) {
            $modules = [];

            foreach (self::GROUPS as $group) {
                $modules += $group;
            }

            self::$modules = $modules;
        }

        return self::$modules;
    }

    /**
     * The group a module belongs to.
     */
    public static function groupOf(string $module): ?string
    {
        foreach (self::GROUPS as $group => $modules) {
            if (array_key_exists($module, $modules)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * The actions a module allows, in the order the form offers them.
     *
     * @return array<int, string>
     */
    public static function typesFor(string $module): array
    {
        return self::modules()[$module] ?? [];
    }

    /**
     * Whether the catalogue declares a module at all.
     */
    public static function hasModule(string $module): bool
    {
        return array_key_exists($module, self::modules());
    }

    /**
     * The key a module's action is granted by — what a role stores.
     */
    public static function key(string $module, string $type): string
    {
        return Str::slug($module) . '.' . Str::lower($type);
    }

    /**
     * What an action lets an officer do, or null for one that explains itself.
     */
    public static function note(string $type): ?string
    {
        return self::NOTES[$type] ?? null;
    }

    /**
     * Every key a module owns.
     *
     * @return array<int, string>
     */
    public static function keysForModule(string $module): array
    {
        return array_map(function (string $type) use ($module) {
            return self::key($module, $type);
        }, self::typesFor($module));
    }

    /**
     * Every key in the catalogue, group by group.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        if (self::$keys === null) {
            $keys = [];

            foreach (self::modules() as $module => $types) {
                $keys = array_merge($keys, self::keysForModule($module));
            }

            self::$keys = $keys;
        }

        return self::$keys;
    }

    /**
     * Whether a key names a permission the catalogue still declares. A key it
     * no longer declares is one a retired module left behind: a role may hold
     * it, but it grants nothing.
     */
    public static function isValid(string $key): bool
    {
        if (self::$keyIndex === null) {
            self::$keyIndex = array_fill_keys(self::keys(), true);
        }

        return isset(self::$keyIndex[$key]);
    }

    /**
     * The catalogue narrowed to a search term — a module is kept while its own
     * name matches, and so is one whose actions match, so "export" finds every
     * module that can be exported.
     *
     * @return array<string, array<string, array<int, string>>>
     */
    public static function search(?string $term): array
    {
        $term = Str::lower(trim((string) $term));

        if ($term === '') {
            return self::GROUPS;
        }

        $matches = [];

        foreach (self::GROUPS as $group => $modules) {
            foreach ($modules as $module => $types) {
                if (self::matches($term, $module) || self::matches($term, implode(' ', $types))) {
                    $matches[$group][$module] = $types;
                }
            }
        }

        return $matches;
    }

    /**
     * Whether a haystack contains the term, case-insensitively.
     */
    private static function matches(string $term, string $haystack): bool
    {
        return strpos(Str::lower($haystack), $term) !== false;
    }
}
