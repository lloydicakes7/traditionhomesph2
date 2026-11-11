<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Expenses\DeleteExpenses;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\Expense;
use App\Queries\Expenses\ExpenseDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * Delete Expense — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#expenseDeleteModal`) with the expense's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. A draft is
 * removed outright; a posted expense has its journal entry reversed first, in
 * the same transaction, so the books never lose a posting.
 */
class ExpenseDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseDeleteModal';

    /** @var int|null Expense the confirmation applies to */
    public $deletingExpenseId = null;

    /** @var string Reference of the expense the confirmation names */
    public $deletingExpenseLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadExpenseDeleteModal'  => 'load',
        'closeExpenseDeleteModal' => 'closeModal',
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

        $this->authorize('delete', Expense::class);

        $expense = $this->records->forEdit($id);

        $this->deletingExpenseId = $expense->id;
        $this->deletingExpenseLabel = $expense->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingExpenseId = null;
        $this->deletingExpenseLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingExpenseId) {
            return;
        }

        $this->authorize('delete', Expense::class);

        try {
            app(DeleteExpenses::class)->handle([$this->deletingExpenseId], auth('web')->user());
        } catch (ValidationException $exception) {
            $this->addError('delete', collect($exception->errors())->flatten()->first());

            return;
        }

        $this->emit('notify', 'success', 'Expense deleted.');
        $this->emit('expense-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.expense-delete-modal');
    }
}
