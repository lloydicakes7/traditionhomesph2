<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\Expense;
use App\Queries\Expenses\ExpenseDetailQuery;

/**
 * View Expense — the full detail view, read-only: the financial summary,
 * the draft-to-approved progress, the category breakdown, and the journal
 * entry accounting posted.
 *
 * Opened client-side by Bootstrap (`#expenseViewModal`) with the expense's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. The record is
 * read on every render rather than held as a public property, so a state change
 * made from the row behind the open dialog is reflected in it.
 */
class ExpenseViewModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseViewModal';

    /** @var int|null Expense being shown */
    public $expenseId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadExpenseViewModal'  => 'load',
        'closeExpenseViewModal' => 'closeModal',
    ];

    /** @var ExpenseDetailQuery */
    protected $records;

    public function boot(ExpenseDetailQuery $records)
    {
        $this->records = $records;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', Expense::class);

        $this->expenseId = (int) $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->expenseId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $expense = $this->loaded && $this->expenseId
            ? $this->records->forView($this->expenseId)
            : null;

        return view('livewire.admin.expenses.modals.expense-view-modal', [
            'expense'   => $expense,
            'breakdown' => $expense ? $this->records->categoryBreakdown($expense) : [],
        ]);
    }
}
