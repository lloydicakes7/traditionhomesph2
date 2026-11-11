<?php

namespace App\Http\Livewire\Admin\Accounting\Transactions;

use App\Enums\JournalSource;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\JournalEntry\JournalLine;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Queries\Accounting\TransactionRegisterQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * The Transactions page — the books read a line at a time.
 *
 * The journal entries page reads a whole posting; this register reads the
 * individual movements, which is what auditing and searching want: everything
 * that moved in a month, everything that hit one account, every receipt from
 * one module — each row an account, an amount and the entry it belongs to, and
 * a link straight into that entry.
 *
 * The page authorizes through the `transaction.*` gates (there is no record of
 * its own to hang a policy on) and only reads: the movements are written where
 * they are raised, each through JournalEntry::post().
 */
class TransactionRegister extends AdminComponent
{
    use WithPagination,
        WithSorting;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Source filter — payment, expense, transfer, manual */
    public $filterSource = '';

    /** @var string Account filter */
    public $filterAccount = '';

    /** @var string Month filter, "Y-m" */
    public $filterMonth = '';

    /** @var string Sort field */
    public $sortField = 'entry_date';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterSource', 'filterAccount',
        'filterMonth', 'sortField', 'sortDirection',
    ];

    /** @var TransactionRegisterQuery */
    protected $transactions;

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(TransactionRegisterQuery $transactions, ChartOfAccountsQuery $chart)
    {
        $this->transactions = $transactions;
        $this->chart = $chart;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterSource(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAccount(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    /**
     * Drop every filter.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterSource = '';
        $this->filterAccount = '';
        $this->filterMonth = '';

        $this->resetPage();
    }

    // =====================================================================
    // Export
    // =====================================================================

    /**
     * Stream the filtered register as a CSV download.
     */
    public function export()
    {
        $this->authorize('transaction.export');

        $rows = collect($this->transactions->exportable($this->filters()))
            ->map(function (JournalLine $line) {
                return [
                    $line->entry && $line->entry->entry_date ? $line->entry->entry_date->format('Y-m-d') : '',
                    $line->entry ? $line->entry->entry_no : '',
                    $line->entry ? (string) $line->entry->memo : '',
                    $line->account ? $line->account->label : (string) $line->account_name,
                    $line->isDebit() ? number_format((float) $line->amount, 2, '.', '') : '',
                    $line->isDebit() ? '' : number_format((float) $line->amount, 2, '.', ''),
                    $line->entry ? $line->entry->source_label : '',
                ];
            })
            ->all();

        $csv = CsvHelper::build(
            ['Date', 'Entry #', 'Description', 'Account', 'Goes To', 'Comes From', 'Source'],
            $rows
        );

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'transactions-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('transaction.viewAny');

        return view('livewire.admin.accounting.transactions.transaction-register', [
            'lines'           => $this->transactions->paginate($this->filters(), $this->sortField, $this->sortDirection),
            'totals'          => $this->transactions->totals($this->filters()),
            'accounts'        => $this->chart->options(),
            'sources'         => JournalSource::labels(),
            'months'          => $this->transactions->months(),
        ]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The page's filters, as the query reads them.
     *
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return [
            'search'  => $this->search,
            'filters' => [
                'source'      => $this->filterSource,
                'account'     => $this->filterAccount,
                'month'       => $this->filterMonth,
            ],
        ];
    }
}
