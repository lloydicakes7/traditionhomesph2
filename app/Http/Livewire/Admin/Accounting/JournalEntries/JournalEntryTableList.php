<?php

namespace App\Http\Livewire\Admin\Accounting\JournalEntries;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\JournalEntryListQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * The Journal Entries page — every posting in the books, in the order it was
 * written: what the modules posted on their own and what the treasurer wrote by
 * hand.
 *
 * A posting is read from the officer's side: what went to which account, what
 * came from which, and how much. The source is what makes the books auditable,
 * so it stays visible on every row and is a filter of its own alongside the
 * classification and period. An entry is opened, edited or printed; a manual
 * entry may be removed from its edit window, while a module's posting stays tied
 * to the record that wrote it.
 *
 * Split (one folder per page):     *   JournalEntryTableList — this component (list state, row actions)
 *   Modals\*              — the journal view and the treasurer's own entry form
 */
class JournalEntryTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithCsvExport;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Source filter — manual, payment, expense, transfer, Cash Funding */
    public $filterSource = '';

    /** @var string Classification filter */
    public $filterClassification = '';

    /** @var string Year filter */
    public $filterPeriod = '';

    /** @var string Sort field */
    public $sortField = 'entry_date';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterSource', 'filterClassification',
        'filterPeriod', 'sortField', 'sortDirection',
    ];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'journal-entry-saved' => '$refresh',
    ];

    /** @var JournalEntryListQuery */
    protected $journalEntries;

    public function boot(JournalEntryListQuery $journalEntries)
    {
        $this->journalEntries = $journalEntries;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterSource(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClassification(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPeriod(): void
    {
        $this->resetPage();
    }

    /**
     * The filters as the query reads them.
     *
     * @return array<string, mixed>
     */
    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'filters'   => [
                'source'         => $this->filterSource,
                'classification' => $this->filterClassification,
                'period'         => $this->filterPeriod,
            ],
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
        ];
    }

    /**
     * Drop every filter — the list's reset control.
     */
    public function clearFilters(): void
    {
        $this->filterSource = '';
        $this->filterClassification = '';
        $this->filterPeriod = '';
        $this->search = '';

        $this->resetPage();
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAuthorizable(): string
    {
        return JournalEntry::class;
    }

    /**
     * The name the download is saved under. Named here rather than derived
     * from a modal prefix, because this page's dialogs name themselves.
     */
    protected function exportFileName(): string
    {
        return 'journal-entry-export-' . now()->format('Y-m-d') . '.csv';
    }

    protected function exportCsv(): string
    {
        $rows = collect($this->journalEntries->exportable($this->criteria()))
            ->map(function (JournalEntry $entry) {
                return [
                    $entry->entry_no,
                    $entry->entry_date_display,
                    $entry->memo,
                    $entry->classification_label,
                    $entry->source_label,
                    $entry->reference_no,
                    $entry->total_amount,
                ];
            })
            ->all();

        return CsvHelper::build(
            ['Entry #', 'Date', 'Memo', 'Classification', 'Source', 'Reference #', 'Amount'],
            $rows
        );
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('livewire.admin.accounting.journal-entries.journal-entry-table-list', [
            'entries'         => $this->journalEntries->paginate($this->criteria()),
            'sources'         => JournalSource::labels(),
            'classifications' => JournalClassification::options(),
            'periods'         => $this->journalEntries->periods(),
            'postedTotals'    => $this->journalEntries->totals($this->criteria()),
        ]);
    }
}
