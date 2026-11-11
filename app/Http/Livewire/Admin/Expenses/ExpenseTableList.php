<?php

namespace App\Http\Livewire\Admin\Expenses;

use App\Enums\ExpenseStatus;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\Admin\Accounting\JournalEntries\Modals\PostToJournalModal;
use App\Http\Livewire\AdminComponent;
use App\Models\Expense\Expense;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Expenses\ExpenseListQuery;
use App\Queries\Expenses\ExpenseOptionQuery;
use App\Utility\CsvHelper;
use Livewire\WithPagination;

/**
 * Expenses page — the complete history of everything the association spends:
 * search, filters, sorting, pagination, selection, and the row actions.
 *
 * Every row action opens its own dialog: the tracking view, the wizard, and the
 * state changes worth confirming — approve and delete — are `data-toggle`
 * triggers that load their record lazily, so the page holds only the list's own
 * state. Mass-delete is one of those triggers, carrying the selection as the
 * loader's `data-id`.
 *
 * Split (one folder per page):
 *   ExpenseTableList   — this component (list state, row actions)
 *   Modals\*           — the page's modals as event-driven siblings
 */
class ExpenseTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithCsvExport,
        WithListCriteria;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Category filter */
    public $filterCategory = '';

    /** @var string Month filter, "Y-m" */
    public $filterMonth = '';

    /** @var string Sort field */
    public $sortField = 'expense_date';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected expense IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterStatus', 'filterCategory', 'filterMonth', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'expense-saved'        => 'refreshList',
        'expense-updated'      => 'refreshList',
        'journal-entry-posted' => 'refreshList',
    ];

    /** @var ExpenseListQuery */
    protected $expenses;

    /** @var ExpenseOptionQuery */
    protected $options;

    public function boot(ExpenseListQuery $expenses, ExpenseOptionQuery $options)
    {
        $this->expenses = $expenses;
        $this->options = $options;
    }

    // =====================================================================
    // Filters — every one of them returns the table to its first page
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Selection ("select all pages")
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->expenses->ids($this->listCriteria());
    }

    /**
     * Toggle "all pages" using the filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * One of the page's dialogs wrote an expense — including the mass-delete
     * dialog. Drop the selection it consumed, so a bulk delete never leaves the
     * toolbar standing over rows that are already gone.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function postToJournal($id): void
    {
        $this->authorize('create', JournalEntry::class);

        $expense = $this->expenses->forPosting((int) $id);

        $this->authorize('view', $expense);

        if (! $expense->status_enum?->is(ExpenseStatus::APPROVED()) || $expense->journal_entry_id) {
            $this->emit('notify', 'warning', 'That expense cannot be posted to the journal.');

            return;
        }

        $this->emit('openPostToJournal', ['source' => 'expense', 'id' => $expense->id]);
        $this->dispatchBrowserEvent('modal-open', ['id' => PostToJournalModal::MODAL_ID]);
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAuthorizable(): string
    {
        return Expense::class;
    }

    protected function exportCsv(): string
    {
        $rows = [];

        foreach ($this->expenses->exportable($this->listCriteria()) as $expense) {
            $rows[] = [
                $expense->expense_date_display,
                $expense->reference,
                $expense->supplier_label,
                $expense->items->pluck('category_name')->unique()->implode('; '),
                $expense->subtotal_display,
                $expense->status_label,
            ];
        }

        return CsvHelper::build([
            'Date', 'Reference No.', 'Supplier', 'Categories',
            'Total', 'Status',
        ], $rows);
    }

    protected function exportFileName(): string
    {
        return 'expense-export-' . now()->format('Y-m-d') . '.csv';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Expense::class);

        return view('livewire.admin.expenses.expense-table-list', [
            'expenses'   => $this->expenses->paginate($this->listCriteria()),
            'statuses'   => ExpenseStatus::labels(),
            'categories' => $this->options->categories(),
            'months'     => $this->options->months(),
            'totals'     => $this->expenses->summary(),
        ]);
    }
}
