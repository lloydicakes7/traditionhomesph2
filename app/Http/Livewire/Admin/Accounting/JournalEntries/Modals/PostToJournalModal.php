<?php

namespace App\Http\Livewire\Admin\Accounting\JournalEntries\Modals;

use App\Actions\Accounting\JournalEntries\PostExpenseJournal;
use App\Actions\Accounting\JournalEntries\PostPaymentJournal;
use App\Enums\ExpenseStatus;
use App\Http\Livewire\AdminModalComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Queries\Collections\Payments\PaymentListQuery;
use App\Queries\Expenses\ExpenseDetailQuery;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PostToJournalModal extends AdminModalComponent
{
    const MODAL_ID = 'postToJournalModal';

    /** @var string payment|expense */
    public $source = '';

    /** @var int|null */
    public $sourceId = null;

    /** @var string */
    public $sourceLabel = '';

    /** @var string */
    public $reference = '';

    /** @var string */
    public $party = '';

    /** @var string */
    public $description = '';

    /** @var string */
    public $entryDate = '';

    /** @var string */
    public $amount = '';

    /** @var string */
    public $account_id = '';

    /** @var array<string, string> */
    protected $listeners = [
        'openPostToJournal'  => 'load',
        'closePostToJournal' => 'closeModal',
    ];

    /** @var PaymentListQuery */
    protected $payments;

    /** @var ExpenseDetailQuery */
    protected $expenses;

    /** @var ChartOfAccountsQuery */
    protected $accounts;

    public function boot(
        PaymentListQuery $payments,
        ExpenseDetailQuery $expenses,
        ChartOfAccountsQuery $accounts
    ) {
        $this->payments = $payments;
        $this->expenses = $expenses;
        $this->accounts = $accounts;
    }

    // ===== Lifecycle =====

    public function load($payload = []): void
    {
        if (! is_array($payload)) {
            return;
        }

        $this->authorize('create', JournalEntry::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->source = (string) ($payload['source'] ?? '');
        $id = (int) ($payload['id'] ?? 0);

        if ($this->source === 'payment') {
            $this->loadPayment($id);
        } elseif ($this->source === 'expense') {
            $this->loadExpense($id);
        } else {
            return;
        }

        if (! $this->sourceId) {
            $this->emit('notify', 'warning', 'That record cannot be posted to the journal.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->account_id = (string) array_key_first($this->accounts->cashOptions());
        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Actions =====

    public function confirm(): void
    {
        if (! $this->loaded || ! $this->sourceId) {
            return;
        }

        $this->authorize('create', JournalEntry::class);

        $this->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('is_cash', 1)],
        ], [
            'account_id.required' => 'Choose the cash or bank account the money moved through.',
        ]);

        try {
            $this->postSource();
        } catch (ValidationException $exception) {
            $this->addError('posting', $exception->errors()[array_key_first($exception->errors())][0] ?? 'The entry could not be posted.');

            return;
        }

        $this->emit('notify', 'success', 'Journal entry posted.');
        $this->emit('journal-entry-saved');
        $this->emit('journal-entry-posted');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Helpers =====

    private function postSource(): void
    {
        if ($this->source === 'payment') {
            $payment = $this->payments->find($this->sourceId);
            $this->authorize('view', $payment);

            app(PostPaymentJournal::class)->handle(
                $payment,
                ['account_id' => $this->account_id],
                auth('web')->user()
            );

            return;
        }

        $expense = $this->expenses->forPosting($this->sourceId);
        $this->authorize('view', $expense);

        app(PostExpenseJournal::class)->handle(
            $expense,
            ['account_id' => $this->account_id],
            auth('web')->user()
        );
    }

    private function loadPayment(int $id): void
    {
        $payment = $this->payments->forPosting($id);
        $this->authorize('view', $payment);

        if (! $payment->is_recorded || $payment->journal_entry_id) {
            return;
        }

        $this->sourceId = $payment->id;
        $this->sourceLabel = 'Payment';
        $this->reference = $payment->receipt_no ?: $payment->bill_reference;
        $this->party = $payment->bill?->issued_to_name ?: '—';
        $this->description = 'Payment for ' . $payment->bill_reference;
        $this->entryDate = $payment->paid_at_display;
        $this->amount = $payment->amount_display;
    }

    private function loadExpense(int $id): void
    {
        $expense = $this->expenses->forPosting($id);
        $this->authorize('view', $expense);

        if (! $expense->status_enum?->is(ExpenseStatus::APPROVED()) || $expense->journal_entry_id) {
            return;
        }

        $this->sourceId = $expense->id;
        $this->sourceLabel = 'Expense';
        $this->reference = $expense->reference;
        $this->party = $expense->supplier_label;
        $this->description = $expense->description ?: 'Association expense';
        $this->entryDate = $expense->expense_date_display;
        $this->amount = $expense->subtotal_display;
    }

    private function resetModalState(): void
    {
        $this->source = '';
        $this->sourceId = null;
        $this->sourceLabel = '';
        $this->reference = '';
        $this->party = '';
        $this->description = '';
        $this->entryDate = '';
        $this->amount = '';
        $this->account_id = '';
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.accounting.journal-entries.modals.post-to-journal-modal', [
            'accounts' => $this->loaded ? $this->accounts->cashOptions() : [],
        ]);
    }
}
