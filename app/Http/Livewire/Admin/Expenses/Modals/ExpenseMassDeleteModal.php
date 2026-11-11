<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\Expenses\DeleteExpenses;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\Expense;
use Illuminate\Validation\ValidationException;

/**
 * Delete Selected Expenses — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list. The records go; posted expenses have their
 * journal entries reversed first, and a closed period refuses the whole
 * selection.
 */
class ExpenseMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete {
        deleteMass as protected deleteMassSelection;
    }

    const MODAL_ID = 'expenseMassDeleteModal';

    /** @var array Expense ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadExpenseMassDeleteModal'  => 'load',
        'closeExpenseMassDeleteModal' => 'closeModal',
    ];

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function deleteMass(): void
    {
        try {
            $this->deleteMassSelection();
        } catch (ValidationException $exception) {
            $this->addError('massDelete', collect($exception->errors())->flatten()->first());
        }
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Expense::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'expenses';
    }

    protected function massDeleteIds(array $ids): int
    {
        return app(DeleteExpenses::class)->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'expense-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.expense-mass-delete-modal');
    }
}
