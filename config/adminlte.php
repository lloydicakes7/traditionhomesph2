<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Tradition Homes',
    'title_prefix' => '',
    'title_postfix' => '| THHA Admin',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'Tradition Homes',
    'logo_img' => 'image/logo.jpg',
    'logo_img_class' => 'brand-image rounded elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'THHA Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'image/logo.jpg',
            'alt' => 'THHA Logo',
            'class' => 'rounded-lg elevation-2',
            'width' => 38,
            'height' => 38,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'image/logo.jpg',
            'alt' => 'THHA Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => '',
    'classes_auth_header' => 'd-flex justify-content-center align-items-center',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => '/admin',
    'logout_url' => 'admin/logout',
    'login_url' => 'admin/login',
    'register_url' => 'admin/register',
    'password_reset_url' => 'admin/password/reset',
    'password_email_url' => 'admin/password/email',
    'password_confirm_url' => 'admin/password/confirm',
    'profile_url' => 'admin/settings/profile',
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Right Navbar items:
        [
            'type' => 'navbar-notification',
            'id' => 'notif-bell',
            'icon' => 'far fa-bell',
            'label_color' => 'danger',
            'url' => 'admin/notifications',
            'topnav_right' => true,
            'dropdown_mode' => true,
            'dropdown_flabel' => 'All notifications',
            'update_cfg' => ['route' => 'admin.notifications.feed', 'period' => 60],
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Search',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.home',
            'icon' => 'fas fa-fw fa-th-large',
        ],

        ['header' => 'OPERATIONS'],

        [
            'text' => 'HOA Center',
            'icon' => 'fas fa-fw fa-building',
            'submenu' => [
                [
                    'text' => 'HOA Members',
                    'route' => 'hoa-center.members',
                    'can' => 'viewAny',
                    'model' => App\Models\HoaMember::class,
                ],
                [
                    'text' => 'Announcements',
                    'route' => 'hoa-center.announcements',
                    'can' => 'viewAny',
                    'model' => App\Models\Announcement::class,
                ],
                [
                    'text' => 'Contents',
                    'route' => 'hoa-center.contents',
                    'can' => 'viewAny',
                    'model' => App\Models\Content::class,
                ],
                [
                    'text' => 'FAQs',
                    'route' => 'hoa-center.faqs',
                    'can' => 'viewAny',
                    'model' => App\Models\Faq::class,
                ],
                [
                    'text' => 'Directory',
                    'route' => 'hoa-center.directory',
                    'can' => 'viewAny',
                    'model' => App\Models\Directory\DirectoryEntry::class,
                ],
            ],
        ],
        [
            'text' => 'Homeowners',
            'icon' => 'fas fa-fw fa-users',
            'submenu' => [
                [
                    'text' => 'Addresses',
                    'route' => 'homeowners.addresses',
                    'can' => 'viewAny',
                    'model' => App\Models\Address::class,
                ],
                [
                    'text' => 'Properties',
                    'route' => 'homeowners.properties',
                    'can' => 'viewAny',
                    'model' => App\Models\Property::class,
                ],
                [
                    'text' => 'Homeowners',
                    'route' => 'homeowners.homeowners',
                    'can' => 'viewAny',
                    'model' => App\Models\User::class,
                ],
                [
                    'text' => 'Households',
                    'route' => 'homeowners.households',
                    'can' => 'viewAny',
                    'model' => App\Models\Household\Household::class,
                ],
                [
                    'text' => 'Residents',
                    'route' => 'homeowners.residents',
                    'can' => 'viewAny',
                    'model' => App\Models\Resident::class,
                ],
            ],
        ],
        [
            'text' => 'Print Documents',
            'icon' => 'fas fa-fw fa-print',
            'can' => 'print.viewAny',
            'submenu' => [
                [
                    'text' => 'Requests Form',
                    'icon' => 'fas fa-fw fa-id-badge',
                    'submenu' => [
                        [
                            'text' => 'Membership Form',
                            'route' => ['print.show', ['document' => 'membership-form']],
                        ],
                        [
                            'text' => 'Business Permit',
                            'route' => ['print.show', ['document' => 'business-permit']],
                        ],
                        [
                            'text' => 'HOA Permit',
                            'route' => ['print.show', ['document' => 'hoa-permit']],
                        ],
                        [
                            'text' => 'Certification',
                            'route' => ['print.show', ['document' => 'certification']],
                        ],
                        [
                            'text' => 'Endorsement Letter',
                            'route' => ['print.show', ['document' => 'endorsement-letter']],
                        ],
                        [
                            'text' => 'Invitation Letter',
                            'route' => ['print.show', ['document' => 'invitation-letter']],
                        ],
                        [
                            'text' => 'Sinumpaang Salaysay',
                            'route' => ['print.show', ['document' => 'sinumpaang-salaysay']],
                        ],
                    ],
                ],
                [
                    'text' => 'Modules',
                    'icon' => 'fas fa-fw fa-file-alt',
                    'submenu' => [
                        [
                            'text' => 'Journal Voucher',
                            'route' => ['print.show', ['document' => 'journal-voucher']],
                        ],
                        [
                            'text' => 'Violation Notice',
                            'route' => ['print.show', ['document' => 'violation-notice']],
                        ],
                    ],
                ],
                [
                    'text' => 'List & Reports',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'submenu' => [
                        [
                            'text' => 'Residents List',
                            'route' => ['print.show', ['document' => 'residents-list']],
                        ],
                        [
                            'text' => 'Occupancy List',
                            'route' => ['print.show', ['document' => 'occupancy-list']],
                        ],
                        [
                            'text' => 'Financial Report',
                            'route' => ['print.show', ['document' => 'financial-report']],
                        ],
                        [
                            'text' => 'Summary Report',
                            'route' => ['print.show', ['document' => 'summary-report']],
                        ],
                    ],
                ],
                [
                    'text' => 'RBI Forms',
                    'icon' => 'fas fa-fw fa-id-card',
                    'can' => 'rbi.viewAny',
                    'submenu' => [
                        [
                            'text' => 'RBI Form A',
                            'route' => 'rbi.form-a',
                        ],
                        [
                            'text' => 'RBI Form B',
                            'route' => 'rbi.form-b',
                        ],
                        [
                            'text' => 'RBI Form C',
                            'route' => 'rbi.form-c',
                            'can' => 'rbi.reports',
                        ],
                    ],
                ],
            ],
        ],
        [
            'text' => 'Amenities Booking',
            'icon' => 'far fa-fw fa-calendar-check',
            'submenu' => [
                [
                    'text' => 'Amenities',
                    'route' => 'amenities.index',
                    'can' => 'viewAny',
                    'model' => App\Models\Amenity\Amenity::class,
                ],
                [
                    'text' => 'Bookings',
                    'route' => 'amenities.bookings',
                    'can' => 'viewAny',
                    'model' => App\Models\Amenity\AmenityBooking::class,
                ],
                [
                    'text' => 'Calendar View',
                    'route' => 'amenities.calendar',
                    'can' => 'viewAny',
                    'model' => App\Models\Amenity\AmenityBooking::class,
                ],

            ],
        ],
        ['header' => 'FINANCE'],

        [
            'text' => 'Collections',
            'icon' => 'fas fa-fw fa-hand-holding-usd',
            'submenu' => [
                [
                    'text' => 'Invoices',
                    'route' => 'collections.invoices',
                    'can' => 'viewAnyInvoices',
                    'model' => App\Models\Bill\Bill::class,
                ],
                [
                    'text' => 'Monthly Dues Invoices',
                    'route' => 'collections.monthly-dues-invoices',
                    'can' => 'viewAnyMonthlyDues',
                    'model' => App\Models\Bill\Bill::class,
                ],
                [
                    'text' => 'Monthly Dues Table View',
                    'route' => 'collections.monthly-dues-table',
                    'can' => 'viewAnyMonthlyDues',
                    'model' => App\Models\Bill\Bill::class,
                ],
                [
                    'text' => 'Monthly Dues Generator',
                    'route' => 'collections.monthly-dues-generator',
                    'can' => 'viewAnyMonthlyDues',
                    'model' => App\Models\Bill\Bill::class,
                ],
                [
                    'text' => 'Payments',
                    'route' => 'collections.payments',
                    'can' => 'viewAny',
                    'model' => App\Models\Payment::class,
                ],
                [
                    'text' => 'Advance Deposits',
                    'route' => 'collections.advance-deposits',
                    'can' => 'viewAny',
                    'model' => App\Models\AdvanceDeposit::class,
                ],
                [
                    'text' => 'Products and Services',
                    'route' => 'collections.products',
                    'can' => 'viewAny',
                    'model' => App\Models\Product\Product::class,
                ],
                [
                    'text' => 'Product Categories',
                    'route' => 'collections.product-categories',
                    'can' => 'viewAny',
                    'model' => App\Models\Product\ProductCategory::class,
                ],
                [
                    'text' => 'Email SOA',
                    'route' => 'collections.soa',
                    'can' => 'viewAny',
                    'model' => App\Models\SoaEmail::class,
                ],
            ],
        ],
        [
            'text' => 'Expenses',
            'icon' => 'fas fa-fw fa-receipt',
            'submenu' => [
                [
                    'text' => 'Expenses',
                    'route' => 'expenses.index',
                    'can' => 'viewAny',
                    'model' => App\Models\Expense\Expense::class,
                ],
                [
                    'text' => 'Expense Categories',
                    'route' => 'expenses.categories',
                    'can' => 'viewAny',
                    'model' => App\Models\Expense\ExpenseCategory::class,
                ],
                [
                    'text' => 'Suppliers',
                    'route' => 'expenses.suppliers',
                    'can' => 'viewAny',
                    'model' => App\Models\Supplier::class,
                ],
            ],
        ],
        [
            'text' => 'Accounting',
            'icon' => 'fas fa-fw fa-balance-scale',
            'submenu' => [
                [
                    'text' => 'Chart of Accounts',
                    'route' => 'accounting.chart-of-accounts',
                    'can' => 'viewAny',
                    'model' => App\Models\Account\Account::class,
                ],
                [
                    'text' => 'Journal Entries',
                    'route' => 'accounting.journal-entries',
                    'can' => 'viewAny',
                    'model' => App\Models\JournalEntry\JournalEntry::class,
                ],
                [
                    'text' => 'Transfers',
                    'route' => 'accounting.transfers',
                    'can' => 'viewAny',
                    'model' => App\Models\Account\AccountTransfer::class,
                ],
                [
                    'text' => 'Cash Funding',
                    'route' => 'accounting.cash-funding',
                    'can' => 'viewAny',
                    'model' => App\Models\JournalEntry\JournalEntry::class,
                ],
                [
                    'text' => 'Transactions',
                    'route' => 'accounting.transactions',
                    'can' => 'transaction.viewAny',
                ],
                [
                    'text' => 'Period Close',
                    'route' => 'accounting.period-close',
                    'can' => 'viewAny',
                    'model' => App\Models\Account\AccountingPeriod::class,
                ],
            ],
        ],

        ['header' => 'REPORTS AND SUPPORT'],

        [
            'text' => 'Supports',
            'icon' => 'fas fa-fw fa-question-circle',
            'submenu' => [
                [
                    'text' => 'Requests',
                    'route' => 'support.requests',
                    'can' => 'viewAny',
                    'model' => App\Models\Request::class,
                ],
                [
                    'text' => 'Complaints',
                    'route' => 'support.complaints',
                    'can' => 'viewAny',
                    'model' => App\Models\Complaint::class,
                ],
                [
                    'text' => 'Violations',
                    'route' => 'support.violations',
                    'can' => 'viewAny',
                    'model' => App\Models\Violation::class,
                ],
            ],
        ],
        [
            'text' => 'Reports',
            'icon' => 'fas fa-fw fa-chart-bar',
            'submenu' => [
                [
                    'text' => 'Community Reports',
                    'route' => 'reports.community',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Residents Report',
                    'route' => 'reports.residents',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Amenities Report',
                    'route' => 'reports.amenities',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'AR Aging Report',
                    'route' => 'reports.ar-aging',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Monthly Income',
                    'route' => 'reports.monthly-income',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Monthly Expenses',
                    'route' => 'reports.monthly-expenses',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Daily Collection Report',
                    'route' => 'reports.daily-collection',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Daily Income Report',
                    'route' => 'reports.daily-income',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Daily Expenses Report',
                    'route' => 'reports.daily-expenses',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Profit & Loss Statement',
                    'route' => 'reports.profit-and-loss',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Statement of Account',
                    'route' => 'reports.statement-of-account',
                    'can' => 'report.viewAny',
                ],
                [
                    'text' => 'Cash Flow Statement',
                    'route' => 'reports.cash-flow',
                    'can' => 'report.viewAny',
                ],
            ],
        ],

        ['header' => 'ADMINISTRATION'],

        [
            'text'  => 'User Manual',
            'route' => 'admin.manual',
            'icon'  => 'fas fa-fw fa-book',
        ],
        [
            'text' => 'User Management',
            'icon' => 'fas fa-fw fa-users-cog',
            'submenu' => [
                [
                    'text' => 'Users',
                    'route' => 'users.index',
                    'can' => 'viewAny',
                    'model' => App\Models\Admin::class,
                ],
                [
                    'text' => 'User Roles',
                    'route' => 'users.roles',
                    'can' => 'manageRoles',
                    'model' => App\Models\Admin::class,
                ],
            ],
        ],

        [
            'text' => 'Settings',
            'icon' => 'fas fa-fw fa-cog',
            'submenu' => [
                [
                    'text' => 'General',
                    'route' => 'settings.general',
                    'can' => 'viewAny',
                    'model' => App\Models\Setting::class,
                ],
                [
                    'text' => 'Collections',
                    'route' => 'settings.collections',
                    'can' => 'viewAny',
                    'model' => App\Models\Setting::class,
                ],
                [
                    'text' => 'Profile',
                    'route' => 'settings.profile',
                    'can' => 'edit',
                    'model' => App\Models\Admin::class
                ],
                [
                    'text' => 'Security',
                    'route' => 'settings.security',
                    'can' => 'viewAny',
                    'model' => App\Models\Setting::class,
                ],
                [
                    'text' => 'Authentication',
                    'route' => 'settings.authentication',
                    'can' => 'viewAny',
                    'model' => App\Models\Setting::class,
                ],
                [
                    'text' => 'Print',
                    'route' => 'settings.print',
                    'can' => 'print.settings.view',
                ],
                [
                    'text' => 'Backup',
                    'route' => 'settings.backup',
                    'can' => 'update',
                    'model' => App\Models\Setting::class,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/select2/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2/css/select2.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2-bootstrap4-theme/css/select2-bootstrap4.min.css',
                ],
            ],
        ],

        'Summernote' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/summernote/summernote-bs4.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/summernote/summernote-bs4.min.js',
                ],
            ],
        ],

        'BsCustomFileInput' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bs-custom-file-input/dist/bs-custom-file-input.min.js',
                ],
            ],
        ],

        'KrajeeFileInput' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/bootstrap-fileinput/css/fileinput.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bootstrap-fileinput/js/fileinput.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bootstrap-fileinput/themes/fa5/theme.min.js',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8.19.0/dist/sweetalert2.min.js',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,
];
