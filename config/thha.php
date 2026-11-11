<?php

return [

    /*
    |--------------------------------------------------------------------------
    | THHA — Tradition Homes HOA Configuration
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | THHA Information
    |--------------------------------------------------------------------------
    */

    'hoa_name' => env('THHA_HOA_NAME', 'Tradition Homes Phase 2 Homeowners Association, Inc.'),
    'hoa_short_name' => env('THHA_HOA_SHORT', 'Tradition Homes'),
    'hoa_description' => env('THHA_HOA_DESCRIPTION', 'Welcome to Tradition Homes Phase 2 Subidvision, Inc. Our low cost housing community where neighbors look out for one another.'),
    'logo_path' => 'image/logo.jpg',

    /*
    |----------------------------------------------------------------------
    | Print Documents
    |----------------------------------------------------------------------
    */

    'hoa_abbr' => env('THHA_HOA_ABBR', 'THHA'),

    'membership_fee' => (float) env('THHA_MEMBERSHIP_FEE', 100),
    'monthly_dues' => (float) env('THHA_MONTHLY_DUES', 50),

    'locale' => [
        'region'   => env('THHA_LOCALE_REGION', 'Region IV-A (CALABARZON)'),
        'province' => env('THHA_LOCALE_PROVINCE', 'Cavite'),
        'city'     => env('THHA_LOCALE_CITY', ''),
        'barangay' => env('THHA_LOCALE_BARANGAY', ''),
        'zip_code' => env('THHA_LOCALE_ZIP', '4107'),
    ],

    // -----------------------------------------------------------------------
    // Money
    // -----------------------------------------------------------------------

    'currency_symbol' => env('THHA_CURRENCY_SYMBOL', '₱'),

    // -----------------------------------------------------------------------
    // Contact Information
    // -----------------------------------------------------------------------

    'address' => env('THHA_ADDRESS', ''),
    'community' => env('THHA_COMMUNITY', ''),
    'website' => env('THHA_WEBSITE', ''),
    'email' => env('THHA_EMAIL', ''),
    'contact_number' => env('THHA_CONTACT', ''),
    'working_hours' => env('THHA_WORKING_HOURS', 'Monday – Friday, 8:00 AM – 5:00 PM'),

    // -----------------------------------------------------------------------
    // Officials
    // -----------------------------------------------------------------------

    'officers' => [
        ['name' => 'Rio B. Genodia', 'position' => 'President'],
        ['name' => 'Pema U. Gallo', 'position' => 'Vice-President'],
        ['name' => 'Leah G. Abatayo', 'position' => 'Secretary'],
        ['name' => 'Wilhelmina M. Carvajal', 'position' => 'Treasurer'],
        ['name' => 'Regina S. Santillana', 'position' => 'Auditor'],
    ],

    'board' => [
        ['name' => 'Reginalda A. Payot', 'position' => 'Livelihood Committee'],
        ['name' => 'Nelson L. Zabate', 'position' => 'Sports and Recreation Committee'],
        ['name' => 'Rolando M. Mirano', 'position' => 'Maintenance Committee'],
        ['name' => 'Medin R. Maraon', 'position' => 'Health and Sanitation Committee'],
        ['name' => 'Rowena L. Echavez', 'position' => 'Socio Cultural Committee'],
        ['name' => 'Divina T. Larong & Lilia R. Bale', 'position' => 'Education and Membership Committee'],
        ['name' => 'Rizza U. Sayre', 'position' => 'Financial Mgmt. Committee'],
        ['name' => 'Merlina G. Acosta', 'position' => 'Grievance and Adjudication Committee'],
        ['name' => 'Bernadette P. Alba', 'position' => 'Peace and Order Committee'],
        ['name' => 'John Paul R. Garrido', 'position' => 'Election Committee'],
        ['name' => 'Jane S. Sajol', 'position' => 'Audit and Inventory Committee'],
        ['name' => 'Primrose L. Doroteo', 'position' => 'GAD Committee'],
        ['name' => 'Paulino P. Coronel', 'position' => 'Kasambahay Committee'],
    ],

    // -----------------------------------------------------------------------
    // Security Settings
    // -----------------------------------------------------------------------

    'security' => [

        // -------------------------------------------------------------------
        // Password Policy
        // -------------------------------------------------------------------

        'password_min_length' => 6,
        'password_require_upper' => true,
        'password_require_lower' => true,
        'password_require_number' => true,
        'password_require_symbol' => true,
        'password_expiry_days' => 0,

        // -------------------------------------------------------------------
        // Login Attempts
        // -------------------------------------------------------------------

        'login_max_attempts' => 5,
        'login_lockout_minutes' => 5,

        // -------------------------------------------------------------------
        // Session Timeout
        // -------------------------------------------------------------------

        'session_timeout' => 120,
        'session_expire_on_close' => false,
    ],
];
