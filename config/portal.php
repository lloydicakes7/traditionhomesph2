<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Homeowner Portal — Portal Chrome Configuration
    |--------------------------------------------------------------------------
    */

    'title_prefix' => '',
    'title_postfix' => ' | Homeowner Portal',
    'classes_body' => 'hold-transition layout-fixed layout-navbar-fixed sidebar-mini',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',

    /*
    |--------------------------------------------------------------------------
    | Navbar Notifications
    |--------------------------------------------------------------------------
    |
    | The portal's notification bell — the same Laravel-AdminLTE notification
    | link the office panel carries in config/adminlte.php, pointed at the
    | portal's own feed. `update_cfg` is what makes it live: the package polls
    | that route and paints the badge and the dropdown from the JSON it answers.
    |
    | `route` is the page the bell's footer opens, and `guard` is the account the
    | bell counts — the portal signs in on `homeowner`, never on `web`.
    |
    */

    'notification' => [
        'id'                   => 'portal-notif-bell',
        'icon'                 => 'far fa-bell',
        'badge_color'          => 'danger',
        'route'                => 'portal.notifications',
        'dropdown_footer_label' => 'All notifications',
        'guard'                => 'homeowner',
        'update_cfg'           => [
            'route'  => 'portal.notifications.feed',
            'period' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' => [
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Search',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'portal.dashboard',
            'icon' => 'fas fa-fw fa-th-large',
        ],

        ['header' => 'SERVICES'],
        [
            'text' => 'My Bookings',
            'route' => 'portal.bookings',
            'icon' => 'far fa-fw fa-calendar-check',
        ],
        [
            'text' => 'Request Documents',
            'route' => 'portal.requests',
            'icon' => 'far fa-fw fa-clipboard',
        ],
        [
            'text' => 'Complaints',
            'route' => 'portal.complaints',
            'icon' => 'fas fa-fw fa-question-circle',
        ],
        [
            'text' => 'Violations',
            'route' => 'portal.violations',
            'icon' => 'fas fa-fw fa-exclamation-triangle',
        ],

        ['header' => 'FINANCE'],
        [
            'text' => 'Statement of Account',
            'route' => 'portal.statement-of-account',
            'icon' => 'fas fa-fw fa-file-invoice-dollar',
        ],
        [
            'text' => 'My Payments',
            'route' => 'portal.payments',
            'icon' => 'far fa-fw fa-credit-card',
        ],

        ['header' => 'MY ACCOUNT'],
        [
            'text' => 'Account Details',
            'route' => 'portal.profile',
            'icon' => 'fas fa-fw fa-id-badge',
        ],
        [
            'text' => 'Personal Information',
            'route' => 'portal.personal-information',
            'icon' => 'fas fa-fw fa-user-edit',
        ],

        [
            'text' => 'Household',
            'route' => 'portal.household-property',
            'icon' => 'fas fa-fw fa-house-user',
        ],
        [
            'text' => 'User Manual',
            'route' => 'portal.manual',
            'icon' => 'fas fa-fw fa-book',
        ],
    ],

];
