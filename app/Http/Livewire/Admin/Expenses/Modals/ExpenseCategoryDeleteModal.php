<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\ExpenseCategories\DeleteExpenseCategories;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\ExpenseCategory;
use App\Queries\Expenses\ExpenseCategoryListQuery;
use Illuminate\Validation\ValidationException;

/**
 * Delete Expense Category — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#expenseCategoryDeleteModal`) with the
 * category's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 * A category only labels spending; one that expenses already use cannot be
 * removed, since there is no disable to fall back on.
 */
class ExpenseCategoryDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseCategoryDeleteModal';

    /** @var int|null Category the confirmation applies to */
    public $deletingCategoryId = null;

    /** @var string Name of the category the confirmation names */
    public $deletingCategoryLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadExpenseCategoryDeleteModal'  => 'load',
        'closeExpenseCategoryDeleteModal' => 'closeModal',
    ];

    /** @var ExpenseCategoryListQuery */
    protected $categories;

    public function boot(ExpenseCategoryListQuery $categories)
    {
        $this->categories = $categories;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', ExpenseCategory::class);

        $category = $this->categories->find($id);

        $this->deletingCategoryId = $category->id;
        $this->deletingCategoryLabel = $category->name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingCategoryId = null;
        $this->deletingCategoryLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingCategoryId) {
            return;
        }

        $this->authorize('delete', ExpenseCategory::class);

        try {
            app(DeleteExpenseCategories::class)->handle([$this->deletingCategoryId], auth('web')->user());
        } catch (ValidationException $exception) {
            $this->addError('delete', collect($exception->errors())->flatten()->first());

            return;
        }

        $this->emit('notify', 'success', 'Category deleted.');
        $this->emit('expense-category-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.expense-category-delete-modal');
    }
}
