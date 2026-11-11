<?php

namespace App\Http\Livewire\Admin\Accounting\Transfers\Modals;

use App\Actions\Accounting\Transfers\RecordTransfer;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\Account;
use App\Models\Account\AccountTransfer;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Utility\MoneyHelper;
use Illuminate\Validation\Rule;

/**
 * Record a transfer — money moved from one of the association's own accounts
 * to another.
 *
 * The officer picks the two accounts, the amount and the day; the dialog shows
 * what each account currently holds so a mistyped account is obvious before the
 * posting is written. Nothing here moves the money itself: the modal hands the
 * figures to the RecordTransfer Action, which lets JournalEntry::post() write the
 * balanced entry.
 *
 * Opened client-side by Bootstrap (`#transferFormModal`) from the page's Record
 * Transfer trigger (`openTransferFormModal`) and loaded lazily by
 * public/js/modal-loader.js. A transfer is never edited — it is removed and
 * recorded again, so there is no id-carrying variant to load.
 */
class TransferFormModal extends AdminModalComponent
{
    const MODAL_ID = 'transferFormModal';

    // The dialog's fields are named after the columns they are written to, so
    // the transfer's own rules validate them without a translation layer.

    /** @var string */
    public $transfer_date = '';

    /** @var string */
    public $from_account_id = '';

    /** @var string */
    public $to_account_id = '';

    /** @var string */
    public $amount = '';

    /** @var string */
    public $memo = '';

    /** @var string */
    public $reference_no = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openTransferFormModal'  => 'openCreate',
        'closeTransferFormModal' => 'closeModal',
    ];

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(ChartOfAccountsQuery $chart)
    {
        $this->chart = $chart;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', AccountTransfer::class);

        $this->transfer_date = now()->toDateString();
        $this->from_account_id = '';
        $this->to_account_id = '';
        $this->amount = '';
        $this->memo = '';
        $this->reference_no = '';
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->transfer_date = now()->toDateString();
        $this->from_account_id = '';
        $this->to_account_id = '';
        $this->amount = '';
        $this->memo = '';
        $this->reference_no = '';
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

        $this->authorize('create', AccountTransfer::class);

        $this->validate($this->rules(), [
            'memo.required'            => 'Say what the money was moved for.',
            'amount.min'               => 'The amount has to be more than zero.',
            'to_account_id.different'  => 'Pick two different accounts.',
            'from_account_id.required' => 'Choose the account the money left.',
            'to_account_id.required'   => 'Choose the account the money arrived in.',
        ]);

        $transfer = app(RecordTransfer::class)->handle([
            'transfer_date'   => $this->transfer_date,
            'from_account_id' => $this->from_account_id,
            'to_account_id'   => $this->to_account_id,
            'amount'          => $this->amount,
            'memo'            => $this->memo,
            'reference_no'    => $this->reference_no,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Transfer ' . $transfer->reference . ' recorded.');
        $this->emit('transfer-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // Accounts and their balances are read only while the dialog is open.
        if (! $this->loaded) {
            return view('livewire.admin.accounting.transfers.modals.transfer-form-modal', [
                'accounts' => [],
                'balances' => [],
            ]);
        }

        return view('livewire.admin.accounting.transfers.modals.transfer-form-modal', [
            'accounts' => $this->chart->cashOptions(),
            'balances' => $this->balances(),
        ]);
    }

    /**
     * What each selectable account holds, so the dialog can show it beside the
     * choices.
     *
     * @return array<int, string>
     */
    private function balances(): array
    {
        $balances = $this->chart->balances();
        $result = [];

        foreach ($this->chart->cashAccounts() as $account) {
            $totals = $balances[$account->id] ?? ['debits' => '0.00', 'credits' => '0.00'];
            $net = bcsub($totals['debits'], $totals['credits'], 2);
            $balance = $account->isDebitNormal() ? $net : bcsub('0.00', $net, 2);

            $result[$account->id] = MoneyHelper::format($balance);
        }

        return $result;
    }

    /**
     * What a transfer has to be: two different accounts, an amount, and the
     * reason the money moved.
     *
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'transfer_date'   => 'required|date',
            'from_account_id' => ['required', Rule::exists('accounts', 'id')],
            'to_account_id'   => ['required', Rule::exists('accounts', 'id'), 'different:from_account_id'],
            'amount'          => 'required|numeric|min:0.01',
            'memo'            => 'required|string|max:255',
            'reference_no'    => 'nullable|string|max:100',
        ];
    }
}
