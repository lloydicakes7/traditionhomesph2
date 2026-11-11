<?php

namespace App\Providers;

use App\Enums\DefaultRoleSlug;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \App\Models\Admin::class => \App\Policies\AdminPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Resident::class => \App\Policies\ResidentPolicy::class,
        \App\Models\Setting::class => \App\Policies\SettingPolicy::class,
        \App\Models\Address::class => \App\Policies\AddressPolicy::class,
        \App\Models\Property::class => \App\Policies\PropertyPolicy::class,
        \App\Models\AdminActivity::class => \App\Policies\AdminActivityPolicy::class,
        \App\Models\Household\Household::class => \App\Policies\HouseholdPolicy::class,
        \App\Models\Directory\DirectoryEntry::class => \App\Policies\DirectoryPolicy::class,
        \App\Models\HoaMember::class => \App\Policies\HoaMemberPolicy::class,
        \App\Models\Announcement::class => \App\Policies\AnnouncementPolicy::class,
        \App\Models\Amenity\Amenity::class => \App\Policies\AmenityPolicy::class,
        \App\Models\Amenity\AmenityBooking::class => \App\Policies\AmenityBookingPolicy::class,
        \App\Models\Bill\Bill::class => \App\Policies\BillPolicy::class,
        \App\Models\Payment::class => \App\Policies\PaymentPolicy::class,
        \App\Models\AdvanceDeposit::class => \App\Policies\AdvanceDepositPolicy::class,
        \App\Models\Product\Product::class => \App\Policies\ProductPolicy::class,
        \App\Models\Product\ProductCategory::class => \App\Policies\ProductCategoryPolicy::class,
        \App\Models\SoaEmail::class => \App\Policies\SoaEmailPolicy::class,
        \App\Models\Expense\Expense::class => \App\Policies\ExpensePolicy::class,
        \App\Models\Expense\ExpenseCategory::class => \App\Policies\ExpenseCategoryPolicy::class,
        \App\Models\Supplier::class => \App\Policies\SupplierPolicy::class,
        \App\Models\Account\Account::class => \App\Policies\AccountPolicy::class,
        \App\Models\JournalEntry\JournalEntry::class => \App\Policies\JournalEntryPolicy::class,
        \App\Models\Account\AccountTransfer::class => \App\Policies\AccountTransferPolicy::class,
        \App\Models\Account\AccountingPeriod::class => \App\Policies\AccountingPeriodPolicy::class,
        \App\Models\Violation::class => \App\Policies\ViolationPolicy::class,
        \App\Models\Complaint::class => \App\Policies\ComplaintPolicy::class,
        \App\Models\Request::class => \App\Policies\RequestPolicy::class,
        \App\Models\Faq::class => \App\Policies\FaqPolicy::class,
        \App\Models\Content::class => \App\Policies\ContentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Reports Policy
        Gate::define('report.viewAny', [\App\Policies\ReportPolicy::class, 'viewAny']);
        Gate::define('report.export', [\App\Policies\ReportPolicy::class, 'export']);

        // Transaction Policy
        Gate::define('transaction.viewAny', [\App\Policies\TransactionPolicy::class, 'viewAny']);
        Gate::define('transaction.export', [\App\Policies\TransactionPolicy::class, 'export']);

        // Print Policy
        Gate::define('print.viewAny', [\App\Policies\PrintPolicy::class, 'viewAny']);
        Gate::define('print.export', [\App\Policies\PrintPolicy::class, 'export']);
        Gate::define('print.settings.view', [\App\Policies\PrintPolicy::class, 'viewSettings']);
        Gate::define('print.settings.update', [\App\Policies\PrintPolicy::class, 'updateSettings']);

        // RBI Policy
        Gate::define('rbi.viewAny', [\App\Policies\RbiReportPolicy::class, 'viewAny']);
        Gate::define('rbi.print', [\App\Policies\RbiReportPolicy::class, 'print']);
        Gate::define('rbi.reports', [\App\Policies\RbiReportPolicy::class, 'view']);
    }
}
