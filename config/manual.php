<?php

/*
|--------------------------------------------------------------------------
| User Manual — Structure
|--------------------------------------------------------------------------
|
| The manual's shape, not its words. This file names the groups and the
| topics in order, declares each admin topic's `abilities` and its `icon`,
| and lists the languages the manual is written in. The copy itself lives in
| `resources/lang/manual/{en,fil}` and is read through the `manual`
| translation namespace, so a translator touches only a language file and the
| catalogue's order is defined once, here.
|
| A topic needs a `key` (which finds its copy and its icon) and an `icon`. An
| admin topic may also declare `abilities`: it is offered when the officer
| passes one of them — the same Policies the panel's own pages answer from —
| and a topic that declares none is offered to everyone. The portal is
| ownership-based, so its topics declare none.
|
| The copy keys are `manual::{side}.title`, `.{intro}`, `.groups.{key}` and
| `.topics.{key}` (a title and its sections); the views' own labels are
| `manual::common.*`. `partials/topic.blade.php` prints a section's blocks in
| a fixed order and decides nothing else.
|
*/

return [

    /*
    | The languages the manual is written in. The key is the locale and the
    | value is the short label the navbar's selector shows (short so the bar
    | still fits a small screen).
    */
    'locales' => [
        'en'  => 'EN',
        'fil' => 'FIL',
    ],

    /* =====================================================================
     *  ADMIN PANEL — /admin/manual
     * =====================================================================
     *
     * Topics follow the panel's own sidebar, module by module. Front matter,
     * the working-by-role chapter and the help chapters declare no abilities,
     * so every officer reads them; a module topic names the abilities that
     * open it.
     */

    'admin' => [

        'groups' => [

            [
                'title'  => 'getting_started',
                'topics' => [
                    ['key' => 'introduction',    'icon' => 'fas fa-fw fa-book'],
                    ['key' => 'getting-started', 'icon' => 'fas fa-fw fa-play-circle'],
                    ['key' => 'dashboard',       'icon' => 'fas fa-fw fa-th-large'],
                ],
            ],

            [
                'title'  => 'operations',
                'topics' => [
                    [
                        'key'       => 'hoa-center',
                        'icon'      => 'fas fa-fw fa-building',
                        'abilities' => [
                            ['viewAny', App\Models\HoaMember::class],
                            ['viewAny', App\Models\Announcement::class],
                            ['viewAny', App\Models\Directory\DirectoryEntry::class],
                        ],
                    ],
                    [
                        'key'       => 'homeowners',
                        'icon'      => 'fas fa-fw fa-users',
                        'abilities' => [
                            ['viewAny', App\Models\Address::class],
                            ['viewAny', App\Models\Property::class],
                            ['viewAny', App\Models\User::class],
                            ['viewAny', App\Models\Household\Household::class],
                            ['viewAny', App\Models\Resident::class],
                        ],
                    ],
                    [
                        'key'       => 'print-documents',
                        'icon'      => 'fas fa-fw fa-print',
                        'abilities' => [
                            ['print.viewAny', null],
                        ],
                    ],
                    [
                        'key'       => 'amenities-booking',
                        'icon'      => 'far fa-fw fa-calendar-check',
                        'abilities' => [
                            ['viewAny', App\Models\Amenity\AmenityBooking::class],
                            ['viewAny', App\Models\Amenity\Amenity::class],
                        ],
                    ],
                ],
            ],

            [
                'title'  => 'finance',
                'topics' => [
                    [
                        'key'       => 'collections',
                        'icon'      => 'fas fa-fw fa-hand-holding-usd',
                        'abilities' => [
                            ['viewAnyInvoices', App\Models\Bill\Bill::class],
                            ['viewAnyMonthlyDues', App\Models\Bill\Bill::class],
                            ['viewAny', App\Models\Payment::class],
                            ['viewAny', App\Models\AdvanceDeposit::class],
                            ['viewAny', App\Models\Product\Product::class],
                            ['viewAny', App\Models\Product\ProductCategory::class],
                            ['viewAny', App\Models\SoaEmail::class],
                        ],
                    ],
                    [
                        'key'       => 'expenses',
                        'icon'      => 'fas fa-fw fa-receipt',
                        'abilities' => [
                            ['viewAny', App\Models\Expense\Expense::class],
                            ['viewAny', App\Models\Expense\ExpenseCategory::class],
                            ['viewAny', App\Models\Supplier::class],
                        ],
                    ],
                    [
                        'key'       => 'accounting',
                        'icon'      => 'fas fa-fw fa-balance-scale',
                        'abilities' => [
                            ['viewAny', App\Models\Account\Account::class],
                            ['viewAny', App\Models\JournalEntry\JournalEntry::class],
                            ['viewAny', App\Models\Account\AccountTransfer::class],
                            ['transaction.viewAny', null],
                            ['viewAny', App\Models\Account\AccountingPeriod::class],
                        ],
                    ],
                ],
            ],

            [
                'title'  => 'reports_and_support',
                'topics' => [
                    [
                        'key'       => 'supports',
                        'icon'      => 'fas fa-fw fa-question-circle',
                        'abilities' => [
                            ['viewAny', App\Models\Request::class],
                            ['viewAny', App\Models\Complaint::class],
                            ['viewAny', App\Models\Violation::class],
                        ],
                    ],
                    [
                        'key'       => 'reports',
                        'icon'      => 'fas fa-fw fa-chart-bar',
                        'abilities' => [
                            ['report.viewAny', null],
                        ],
                    ],
                ],
            ],

            [
                'title'  => 'administration',
                'topics' => [
                    [
                        'key'       => 'user-management',
                        'icon'      => 'fas fa-fw fa-users-cog',
                        'abilities' => [
                            ['viewAny', App\Models\Admin::class],
                            ['manageRoles', App\Models\Admin::class],
                        ],
                    ],
                    [
                        'key'       => 'settings',
                        'icon'      => 'fas fa-fw fa-cog',
                        'abilities' => [
                            ['viewAny', App\Models\Setting::class],
                        ],
                    ],
                ],
            ],

            [
                'title'  => 'working_by_role',
                'topics' => [
                    ['key' => 'by-role', 'icon' => 'fas fa-fw fa-user-shield'],
                ],
            ],

            [
                'title'  => 'help',
                'topics' => [
                    ['key' => 'troubleshooting', 'icon' => 'fas fa-fw fa-life-ring'],
                    ['key' => 'faq',             'icon' => 'fas fa-fw fa-question'],
                    ['key' => 'glossary',        'icon' => 'fas fa-fw fa-spell-check'],
                ],
            ],

        ],
    ],

    /* =====================================================================
     *  HOMEOWNER PORTAL — /portal/manual
     * =====================================================================
     *
     * The portal is ownership-based, so every topic is offered to any
     * signed-in homeowner and no topic declares abilities.
     */

    'portal' => [

        'groups' => [

            [
                'title'  => 'getting_started',
                'topics' => [
                    ['key' => 'introduction',    'icon' => 'fas fa-fw fa-book'],
                    ['key' => 'getting-started', 'icon' => 'fas fa-fw fa-play-circle'],
                    ['key' => 'dashboard',       'icon' => 'fas fa-fw fa-th-large'],
                ],
            ],

            [
                'title'  => 'services',
                'topics' => [
                    ['key' => 'bookings',   'icon' => 'far fa-fw fa-calendar-check'],
                    ['key' => 'requests',   'icon' => 'far fa-fw fa-clipboard'],
                    ['key' => 'complaints', 'icon' => 'fas fa-fw fa-question-circle'],
                    ['key' => 'violations', 'icon' => 'fas fa-fw fa-exclamation-triangle'],
                ],
            ],

            [
                'title'  => 'finance',
                'topics' => [
                    ['key' => 'statement-of-account', 'icon' => 'fas fa-fw fa-file-invoice-dollar'],
                    ['key' => 'payments',             'icon' => 'far fa-fw fa-credit-card'],
                ],
            ],

            [
                'title'  => 'my_account',
                'topics' => [
                    ['key' => 'profile',              'icon' => 'fas fa-fw fa-id-badge'],
                    ['key' => 'personal-information', 'icon' => 'fas fa-fw fa-user-edit'],
                    ['key' => 'household',            'icon' => 'fas fa-fw fa-house-user'],
                ],
            ],

            [
                'title'  => 'help',
                'topics' => [
                    ['key' => 'troubleshooting', 'icon' => 'fas fa-fw fa-life-ring'],
                    ['key' => 'faq',             'icon' => 'fas fa-fw fa-question'],
                    ['key' => 'glossary',        'icon' => 'fas fa-fw fa-spell-check'],
                ],
            ],

        ],
    ],

];
