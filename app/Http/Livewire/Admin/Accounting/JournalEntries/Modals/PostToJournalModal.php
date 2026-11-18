<?php

namespace App\Http\Livewire\Admin\Accounting\JournalEntries\Modals;

use App\Actions\Accounting\JournalEntries\PostExpenseJournal;
use App\Actions\Accounting\JournalEntries\PostPaymentJournal;
use App\Enums\JournalClassification;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\EditsJournalLines;
use App\Models\Account\Account;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Queries\Accounting\JournalPostingPreviewQuery;
use App\Queries\Collections\Payments\PaymentListQuery;
use App\Queries\Expenses\ExpenseDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * Post to Journal — the entry a payment or an expense carries.
 *
 * The dialog opens on the entry the system would write for the record: the
 * date, reference, memo and both sides of the entry are filled in from the
 * payment or the expense. Every field stays editable, so accounting can point
 * a side at another cash account, split an amount or reword the memo before
 * posting. What the officer sees is what reaches the books.
 */
class PostToJournalModal extends AdminModalComponent
{
    use EditsJournalLines;

    const MODAL_ID = 'postToJournalModal';

    /** @var string payment|expense */
    public $source = '';

    /** @var int|null */
    public $sourceId = null;

    /** @var string */
    public $entry_date = '';

    /** @var string */
    public $classification = JournalClassification::STANDARD;

    /** @var string */
    public $reference_no = '';

    /** @var string */
    public $memo = '';

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

    /** @var JournalPostingPreviewQuery */
    protected $previews;

    /** @var int The cash account the source names, when it names one */
    private $moneyId = 0;

    public function boot(
        PaymentListQuery $payments,
        ExpenseDetailQuery $expenses,
        ChartOfAccountsQuery $accounts,
        JournalPostingPreviewQuery $previews
    ) {
        $this->payments = $payments;
        $this->expenses = $expenses;
        $this->accounts = $accounts;
        $this->previews = $previews;
    }

    // =====================================================================
    // Lifecycle
    // =====================================================================

    public function load($payload = []): void
    {
        if (! is_array($payload)) {
            return;
        }

        $this->authorize('create', JournalEntry::class);

        $this->resetModal();
        $this->resetValidation();

        $this->source = (string) ($payload['source'] ?? '');
        $id = (int) ($payload['id'] ?? 0);

        if ($this->source === 'payment') {
            $this->sourceId = $this->postablePaymentId($id);
        } elseif ($this->source === 'expense') {
            $this->sourceId = $this->postableExpenseId($id);
        } else {
            return;
        }

        if (! $this->sourceId) {
            $this->refuse('That record cannot be posted to the journal.');

            return;
        }

        $money = $this->moneyAccount();

        if (! $money) {
            $this->refuse('Set up a cash or bank account in the chart of accounts before posting.');

            return;
        }

        $this->fillEntry($this->previewFor($money));
        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->source = '';
        $this->sourceId = null;
        $this->moneyId = 0;
        $this->entry_date = now()->toDateString();
        $this->classification = JournalClassification::STANDARD;
        $this->reference_no = '';
        $this->memo = '';
        $this->lines = [$this->blankLine(), $this->blankLine(JournalLine::SIDE_CREDIT)];

        parent::resetModal();
    }

    // =====================================================================
    // Actions
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded || ! $this->sourceId) {
            return;
        }

        $this->authorize('create', JournalEntry::class);

        $this->normalizeLines();
        $this->validate($this->rules(), $this->messages());

        if (! $this->isBalanced()) {
            throw ValidationException::withMessages([
                'lines' => 'The entry is not balanced — what goes to the accounts must equal what comes from them.',
            ]);
        }

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

    // =====================================================================
    // Rules
    // =====================================================================

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'entry_date'     => 'required|date',
            'classification' => 'required|in:' . implode(',', array_keys(JournalClassification::options())),
            'reference_no'   => 'nullable|string|max:100',
            'memo'           => 'required|string|max:255',
        ] + $this->lineRules();
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'entry_date.required'         => 'Give the entry a date.',
            'memo.required'               => 'Write a memo for this entry.',
            'lines.required'              => 'Add at least two lines.',
            'lines.min'                   => 'A journal entry needs at least two lines.',
            'lines.*.account_id.required' => 'Choose an account for every line.',
            'lines.*.amount.required'     => 'Enter an amount for every line.',
            'lines.*.amount.min'          => 'Every line must be greater than zero.',
            'lines.*.side.required'       => 'Choose whether the amount goes to or comes from the account.',
        ];
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function postablePaymentId(int $id): ?int
    {
        $payment = $this->payments->forPosting($id);
        $this->authorize('view', $payment);

        if (! $payment->is_recorded || $payment->journal_entry_id) {
            return null;
        }

        $this->moneyId = (int) $payment->paymentMethod?->account_id;

        return $payment->id;
    }

    private function postableExpenseId(int $id): ?int
    {
        $expense = $this->expenses->forPosting($id);
        $this->authorize('view', $expense);

        if ($expense->journal_entry_id) {
            return null;
        }

        return $expense->id;
    }

    private function moneyAccount(): ?Account
    {
        $cash = $this->accounts->cashAccounts();
        $preferred = $cash->firstWhere('id', $this->moneyId);

        return $preferred ?: $cash->first();
    }

    private function previewFor(Account $money): array
    {
        if ($this->source === 'payment') {
            return $this->previews->payment($this->payments->forPosting($this->sourceId), $money);
        }

        return $this->previews->expense($this->expenses->forPosting($this->sourceId), $money);
    }

    private function fillEntry(array $preview): void
    {
        $this->entry_date = $preview['entry_date'] ? $preview['entry_date']->format('Y-m-d') : now()->toDateString();
        $this->classification = $preview['classification'];
        $this->reference_no = $preview['reference_no'];
        $this->memo = $preview['memo'];
        $this->lines = $preview['lines'];
    }

    private function postSource(): void
    {
        $entry = [
            'entry_date'     => $this->entry_date,
            'classification' => $this->classification,
            'reference_no'   => $this->reference_no,
            'memo'           => $this->memo,
            'lines'          => $this->journalLines(),
        ];

        if ($this->source === 'payment') {
            $payment = $this->payments->forPosting($this->sourceId);
            $this->authorize('view', $payment);

            app(PostPaymentJournal::class)->handle($payment, $entry, auth('web')->user());

            return;
        }

        $expense = $this->expenses->forPosting($this->sourceId);
        $this->authorize('view', $expense);

        app(PostExpenseJournal::class)->handle($expense, $entry, auth('web')->user());
    }

    private function refuse(string $message): void
    {
        $this->sourceId = null;
        $this->emit('notify', 'warning', $message);
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.journal-entries.modals.post-to-journal-modal', [
            'classifications'      => JournalClassification::options(),
            'manualAccountOptions' => $this->loaded ? $this->manualAccountOptions() : [],
            'totals'               => $this->totals(),
            'balanced'             => $this->isBalanced(),
        ]);
    }
}
