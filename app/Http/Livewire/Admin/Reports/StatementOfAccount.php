<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\Concerns\ExportsReportCsv;
use App\Http\Livewire\AdminComponent;
use App\Queries\Collections\Soa\StatementDataQuery;
use App\Utility\CsvHelper;
use App\Utility\PeriodHelper;

/**
 * The Statement of Account — one homeowner's account for one period, as the
 * office would put it in a homeowner's hands: what was billed, what it
 * settled, and what is still outstanding.
 *
 * It is the same reading the Email SOA page previews and mails
 * (`StatementDataQuery::forUser()`), so a statement printed here and a
 * statement mailed to the homeowner cannot say different things.
 *
 * The picker lists homeowner accounts rather than units: a bill belongs to an
 * account, so a homeowner who has something due but occupies no household is
 * stated here all the same. The page opens on no account rather than on
 * whichever one happens to be first: the office reads the statement of the
 * account it names, and until it names one the picker says so instead of
 * quietly stating somebody else's account.
 */
class StatementOfAccount extends AdminComponent
{
    use ExportsReportCsv;

    /** @var int|string */
    public $userId = '';

    /** @var string The billable month, "Y-m" */
    public $period = '';

    /** @var StatementDataQuery */
    protected $statements;

    public function boot(StatementDataQuery $statements)
    {
        $this->statements = $statements;
    }

    public function mount(): void
    {
        $this->period = PeriodHelper::current();
    }

    /**
     * The statement the page is showing, or null while no account is picked.
     *
     * @return array<string, mixed>|null
     */
    private function statement(): ?array
    {
        if (! $this->userId) {
            return null;
        }

        return $this->statements->forUser((int) $this->userId, $this->period ?: null);
    }

    /**
     * The homeowner accounts the picker offers: every account with something
     * still to pay, whether or not it occupies a household.
     *
     * @return array<int, string>
     */
    private function homeownerOptions(): array
    {
        return $this->statements->accounts();
    }

    /**
     * What the page says while it has no statement to read: that no account
     * has anything outstanding at all, or that one has yet to be picked.
     *
     * @param  array<int, string>  $homeowners
     */
    private function emptyMessage(array $homeowners): string
    {
        if (empty($homeowners)) {
            return 'No statement to read — no homeowner has an outstanding bill.';
        }

        return 'Pick a homeowner to read their statement of account.';
    }

    protected function reportKey(): string
    {
        return 'statement-of-account';
    }

    protected function exportCsv(): string
    {
        $statement = $this->statement();

        if (! $statement) {
            return CsvHelper::build(['Property', 'Homeowner', 'Period', 'Billed', 'Amount', 'Balance'], []);
        }

        $rows = [];

        foreach ($statement['bills'] as $bill) {
            $particulars = $bill->items->pluck('name')->implode(', ');

            $rows[] = [
                $statement['property'],
                $statement['owner'],
                $bill->billing_period_display !== '—' ? $bill->billing_period_display : $statement['period'],
                ($particulars !== '' ? $particulars : $bill->type_label) . ' (' . $bill->reference . ')',
                number_format((float) $bill->total, 2, '.', ''),
                number_format((float) $bill->balance, 2, '.', ''),
            ];
        }

        $rows[] = [$statement['property'], $statement['owner'], $statement['period'], 'TOTAL BALANCE DUE', '', number_format($statement['balance'], 2, '.', '')];

        return CsvHelper::build(
            ['Property', 'Homeowner', 'Period', 'Billed', 'Amount', 'Balance'],
            $rows
        );
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        $homeowners = $this->homeownerOptions();

        return view('livewire.admin.reports.statement-of-account', [
            'statement'    => $this->statement(),
            'homeowners'   => $homeowners,
            'periods'      => PeriodHelper::recentMonths(),
            'emptyMessage' => $this->emptyMessage($homeowners),
        ]);
    }
}
