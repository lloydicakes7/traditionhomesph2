<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Expenses\ApproveExpense;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\Expense;
use App\Queries\Expenses\ExpenseDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * Approve Expense — the confirmation a draft row's Approve action opens.
 *
 * Opened client-side by Bootstrap (`#expenseApproveModal`) with the expense's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Signing off
 * moves the expense from Draft to Approved and locks it; accounting posts its
 * journal entry separately from the expenses list.
 */
class ExpenseApproveModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseApproveModal';

    /** @var int|null Expense the confirmation applies to */
    public $approvingExpenseId = null;

    /** @var string Reference of the expense the confirmation names */
    public $approvingExpenseLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadExpenseApproveModal'  => 'load',
        'closeExpenseApproveModal' => 'closeModal',
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

        $this->authorize('approve', Expense::class);

        $expense = $this->records->forEdit($id);

        $this->approvingExpenseId = $expense->id;
        $this->approvingExpenseLabel = $expense->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->approvingExpenseId = null;
        $this->approvingExpenseLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Approve
    // =====================================================================

    public function approve(): void
    {
        if (! $this->loaded || ! $this->approvingExpenseId) {
            return;
        }

        $this->authorize('approve', Expense::class);

        try {
            $expense = app(ApproveExpense::class)->handle(
                $this->records->forEdit($this->approvingExpenseId),
                auth('web')->user()
            );
        } catch (ValidationException $exception) {
            $this->addError('approve', collect($exception->errors())->flatten()->first());

            return;
        }

        $this->emit('notify', 'success', 'Expense ' . $expense->reference . ' approved and ready for posting.');
        $this->emit('expense-updated');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.expense-approve-modal');
    }
}
