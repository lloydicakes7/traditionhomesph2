<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues;

use App\Actions\Collections\MonthlyDues\ApplyAdvanceDepositsToDues;
use App\Actions\Collections\MonthlyDues\GenerateMonthlyDues;
use App\Http\Livewire\AdminComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\MonthlyDueGenerationQuery;
use App\Utility\PeriodHelper;
use Livewire\WithPagination;

/**
 * Monthly Dues Generator — where a month is billed.
 *
 * The page shows the rate configuration in force, what each property's due
 * comes to under it, and what the whole month adds up to, so the officer can
 * see exactly what pressing Generate will raise before it is raised. Only what
 * has not been billed is raised: a property already carrying that month is
 * skipped rather than charged twice.
 *
 * The second action settles a month from the credit homeowners hold — the
 * deposits recorded on the Advance Deposits page — oldest deposit first,
 * through the same ledger every other receipt goes through.
 */
class MonthlyDuesGenerator extends AdminComponent
{
    use WithPagination;

    /** @var string The billing month being generated, "Y-m" */
    public $month = '';

    /** @var MonthlyDueGenerationQuery */
    protected $query;

    /** @var GenerateMonthlyDues */
    protected $generateDues;

    /** @var ApplyAdvanceDepositsToDues */
    protected $applyDeposits;

    public function boot(
        MonthlyDueGenerationQuery $query,
        GenerateMonthlyDues $generateDues,
        ApplyAdvanceDepositsToDues $applyDeposits
    ) {
        $this->query = $query;
        $this->generateDues = $generateDues;
        $this->applyDeposits = $applyDeposits;
    }

    public function mount(): void
    {
        $this->month = PeriodHelper::current();
    }

    /**
     * A new month is a new list — start it on its first page.
     */
    public function updatingMonth(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Actions
    // =====================================================================

    /**
     * Raise the month's dues for every property not already billed for it.
     */
    public function generate(): void
    {
        $this->authorize('generateMonthlyDues', Bill::class);

        $result = $this->generateDues->handle($this->month, auth('web')->user());

        if ($result['created'] === 0) {
            $this->emit('notify', 'warning', 'Every property is already billed for this month.');

            return;
        }

        $message = $result['created'] . ' invoice(s) generated.';

        if ($result['skipped'] > 0) {
            $message .= ' ' . $result['skipped'] . ' already billed and skipped.';
        }

        $this->emit('notify', 'success', $message);
    }

    /**
     * Settle the month from the credit each unit holds.
     */
    public function applyAdvancePayment(): void
    {
        $this->authorize('applyAdvancePayment', Bill::class);

        $drawn = $this->applyDeposits->handle($this->month, auth('web')->user());

        $this->emit(
            'notify',
            $drawn > 0 ? 'success' : 'warning',
            $drawn > 0
                ? $drawn . ' payment(s) drawn from advance deposits.'
                : 'No credit was available to apply to this month.'
        );
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        return view(
            'livewire.admin.collections.monthly-dues.monthly-dues-generator',
            $this->query->preview($this->month, $this->page)
        );
    }
}
