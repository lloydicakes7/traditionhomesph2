<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Actions\Expenses\ExpenseCategories\CreateExpenseCategory;
use App\Actions\Expenses\ExpenseCategories\UpdateExpenseCategory;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\ExpenseCategory;
use App\Queries\Expenses\ExpenseCategoryListQuery;

/**
 * Add / Edit Expense Category — one form for the office's own filing labels:
 * a name and an optional note.
 *
 * Opened client-side by Bootstrap (`#expenseCategoryFormModal`): a blank dialog
 * from the page's Add Category trigger (`openExpenseCategoryFormModal`), a
 * pre-filled one carrying the category's id (`loadExpenseCategoryFormModal`),
 * both relayed by public/js/modal-loader.js.
 */
class CategoryFormModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseCategoryFormModal';

    /** @var bool Editing an existing category when true */
    public $isEditing = false;

    /** @var int|null Category being edited; null when adding a new one */
    public $editingCategoryId = null;

    /** @var array<string, mixed> The category's own columns as form state */
    public $form = [
        'name'        => '',
        'description' => '',
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openExpenseCategoryFormModal'  => 'openCreate',
        'loadExpenseCategoryFormModal'  => 'openEdit',
        'closeExpenseCategoryFormModal' => 'closeModal',
    ];

    /** @var ExpenseCategoryListQuery */
    protected $categories;

    public function boot(ExpenseCategoryListQuery $categories)
    {
        $this->categories = $categories;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'form.name'        => ['required', 'string', 'max:150'],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Short, distinct messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.name.required' => 'Give the category a name.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'form.name'        => 'name',
            'form.description' => 'description',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', ExpenseCategory::class);

        $this->isEditing = false;
        $this->editingCategoryId = null;
        $this->form = [
            'name'        => '',
            'description' => '',
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', ExpenseCategory::class);

        $category = $this->categories->find($id);

        $this->isEditing = true;
        $this->editingCategoryId = $category->id;
        $this->form = [
            'name'        => (string) $category->name,
            'description' => (string) $category->description,
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingCategoryId = null;
        $this->form = [
            'name'        => '',
            'description' => '',
        ];
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->isEditing
            ? $this->authorize('update', ExpenseCategory::class)
            : $this->authorize('create', ExpenseCategory::class);

        $validated = $this->validate($this->rules(), $this->messages());
        $data = $validated['form'];

        if ($this->isEditing) {
            $category = app(UpdateExpenseCategory::class)->handle(
                $this->categories->find($this->editingCategoryId),
                $data,
                auth('web')->user()
            );

            $this->emit('notify', 'success', 'Category ' . $category->name . ' updated.');
        } else {
            $category = app(CreateExpenseCategory::class)->handle($data, auth('web')->user());

            $this->emit('notify', 'success', 'Category ' . $category->name . ' added.');
        }

        $this->emit('expense-category-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.expenses.modals.category-form-modal', [
            'editing' => $this->isEditing,
        ]);
    }
}
