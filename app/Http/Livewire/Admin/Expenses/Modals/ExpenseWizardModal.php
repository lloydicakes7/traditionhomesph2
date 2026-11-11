<?php

namespace App\Http\Livewire\Admin\Expenses\Modals;

use App\Enums\ExpenseStatus;
use App\Actions\Expenses\Expenses\LogExpense;
use App\Actions\Expenses\Expenses\UpdateExpense;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Expense\Expense;
use App\Queries\Expenses\ExpenseDetailQuery;
use App\Queries\Expenses\ExpenseOptionQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;

/**
 * Record Expense — the two-step wizard the office logs spending through.
 *
 * Step one is the details (reference number is generated on save, supplier,
 * general description); step two is the line items by category, whose total
 * adds itself up as the rows change. Recording an expense does not touch the
 * books: accounting posts it separately from the expenses list.
 *
 * Editing a draft re-opens the same wizard pre-filled; an approved expense is
 * locked by the Action.
 *
 * Opened client-side by Bootstrap (`#expenseWizardModal`): a blank dialog from
 * the page's Record Expense trigger (`openExpenseWizardModal`), a pre-filled
 * one carrying the expense's id (`loadExpenseWizardModal`), both relayed by
 * public/js/modal-loader.js.
 */
class ExpenseWizardModal extends AdminModalComponent
{
    const MODAL_ID = 'expenseWizardModal';

    /** @var int|null Expense being edited; null when logging a new one */
    public $expenseId = null;

    /** @var string|null Reference of the expense being edited */
    public $expenseReference = null;

    /** @var int Which step of the wizard is showing: 1|2 */
    public $step = 1;

    /** @var array<string, mixed> Step one — the details */
    public $form = [
        'expense_date' => '',
        'supplier_id'  => '',
        'description'  => '',
    ];

    /** @var array<int, array<string, mixed>> Step two — the lines */
    public $items = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openExpenseWizardModal'  => 'openCreate',
        'loadExpenseWizardModal'  => 'openEdit',
        'closeExpenseWizardModal' => 'closeModal',
    ];

    /** @var ExpenseDetailQuery */
    protected $records;

    /** @var ExpenseOptionQuery */
    protected $options;

    public function boot(ExpenseDetailQuery $records, ExpenseOptionQuery $options)
    {
        $this->records = $records;
        $this->options = $options;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    /**
     * What the whole wizard accepts.
     *
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'form.expense_date'       => ['required', 'date'],
            'form.supplier_id'        => ['nullable', Rule::exists('suppliers', 'id')],
            'form.description'        => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.expense_category_id' => ['nullable', Rule::exists('expense_categories', 'id')],
            'items.*.description'     => ['required', 'string', 'max:500'],
            'items.*.amount'          => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * What each step validates on the way through.
     *
     * @return array<string, mixed>
     */
    private function stepRules(int $step): array
    {
        $rules = $this->rules();

        if ($step === 1) {
            return array_intersect_key($rules, [
                'form.expense_date' => null,
                'form.supplier_id'  => null,
                'form.description'  => null,
            ]);
        }

        return array_intersect_key($rules, [
            'items'              => null,
            'items.*.expense_category_id' => null,
            'items.*.description' => null,
            'items.*.amount'      => null,
        ]);
    }

    /**
     * Short, distinct messages — the field says what is wrong with it and
     * nothing more.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.expense_date.required' => 'Enter the date of the expense.',
            'items.required'             => 'Add at least one line item.',
            'items.*.expense_category_id.exists' => 'Pick a category for this line.',
            'items.*.description.required' => 'Describe what this line is for.',
            'items.*.amount.required'    => 'Enter the amount.',
            'items.*.amount.min'         => 'The amount must be more than zero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'form.expense_date'       => 'expense date',
            'form.supplier_id'        => 'supplier',
            'form.description'        => 'description',
            'items.*.expense_category_id' => 'category',
            'items.*.description'     => 'description',
            'items.*.amount'          => 'amount',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    /**
     * Open the wizard blank for a new expense.
     */
    public function openCreate(): void
    {
        $this->authorize('create', Expense::class);

        $this->expenseId = null;
        $this->step = 1;
        $this->form = [
            'expense_date' => now()->format('Y-m-d'),
            'supplier_id'  => '',
            'description'  => '',
        ];
        $this->items = [$this->blankItem()];
        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Open the wizard pre-filled for a draft expense.
     *
     * @param  int|null  $id
     */
    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Expense::class);

        $expense = $this->records->forEdit($id);

        if (! $expense->status_enum || ! $expense->status_enum->is(ExpenseStatus::DRAFT())) {
            $this->emit('notify', 'warning', 'Only a draft expense can be edited.');

            return;
        }

        $this->expenseId = $expense->id;
        $this->expenseReference = $expense->reference;
        $this->step = 1;
        $this->form = [
            'expense_date' => $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d'),
            'supplier_id'  => (string) ($expense->supplier_id ?: ''),
            'description'  => (string) $expense->description,
        ];
        $this->items = $expense->items->map(function ($item) {
            return [
                'expense_category_id' => (string) ($item->expense_category_id ?: ''),
                'description'         => (string) $item->description,
                'amount'              => (float) $item->amount,
            ];
        })->all();
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->expenseId = null;
        $this->expenseReference = null;
        $this->step = 1;
        $this->form = [
            'expense_date' => '',
            'supplier_id'  => '',
            'description'  => '',
        ];
        $this->items = [];
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Steps
    // =====================================================================

    /**
     * Validate the current step, then move forward.
     */
    public function nextStep(): void
    {
        $this->validate($this->stepRules($this->step), $this->messages());

        $this->step = min(2, $this->step + 1);
    }

    /**
     * Move back a step, keeping what was typed.
     */
    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    /**
     * Jump straight to a step the officer has already reached — backwards
     * only, or into a step whose fields have already passed validation.
     */
    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;

            return;
        }

        for ($current = $this->step; $current < $step; $current++) {
            $this->validate($this->stepRules($current), $this->messages());
        }

        $this->step = $step;
    }

    // =====================================================================
    // Lines
    // =====================================================================

    /**
     * Add an empty line.
     */
    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    /**
     * Drop a line.
     *
     * @param  int  $index
     */
    public function removeItem($index): void
    {
        unset($this->items[$index]);

        $this->items = array_values($this->items);
    }

    // =====================================================================
    // Save
    // =====================================================================

    /**
     * Save the expense through the whole wizard's rules.
     */
    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->expenseId
            ? $this->authorize('update', Expense::class)
            : $this->authorize('create', Expense::class);

        $this->validate($this->rules(), $this->messages());

        $payload = [
            'expense_date' => $this->form['expense_date'],
            'supplier_id'  => $this->form['supplier_id'] ?? null,
            'description'  => $this->form['description'] ?: null,
            'items'        => $this->items,
        ];

        if ($this->expenseId) {
            $expense = app(UpdateExpense::class)->handle(
                $this->records->forEdit($this->expenseId),
                $payload,
                auth('web')->user()
            );

            $this->emit('notify', 'success', 'Expense ' . $expense->reference . ' updated.');
        } else {
            $expense = app(LogExpense::class)->handle($payload, auth('web')->user());

            $this->emit('notify', 'success', 'Expense ' . $expense->reference . ' logged.');
        }

        $this->emit('expense-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * A blank line.
     *
     * @return array<string, mixed>
     */
    private function blankItem(): array
    {
        return ['expense_category_id' => '', 'description' => '', 'amount' => 0];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // The categories and suppliers the wizard offers are read only while
        // it is on screen — the shell's first render still compiles the hidden
        // content, so it is handed an empty state until the record is loaded.
        if (! $this->loaded) {
            return view('livewire.admin.expenses.modals.expense-wizard-modal', [
                'categories'      => [],
                'suppliers'       => [],
                'subtotal'        => 0.0,
                'subtotalDisplay' => MoneyHelper::format(0),
                'editing'         => false,
                'expenseReference'=> null,
            ]);
        }

        $subtotal = 0.0;

        foreach ($this->items as $item) {
            $subtotal += (float) ($item['amount'] ?? 0);
        }

        return view('livewire.admin.expenses.modals.expense-wizard-modal', [
            'categories'      => $this->options->categories(),
            'suppliers'       => $this->options->suppliers(),
            'subtotal'        => round($subtotal, 2),
            'subtotalDisplay'  => MoneyHelper::format(round($subtotal, 2)),
            'editing'          => (bool) $this->expenseId,
            'expenseReference' => $this->expenseReference,
        ]);
    }
}
