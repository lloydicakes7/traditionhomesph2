<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Actions\Collections\AdvanceDeposits\ApplyAdvanceDeposit;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;
use App\Queries\Collections\Payments\BillOptionQuery;

/**
 * Apply Advance Payment — settle part of a bill from the credit an account
 * holds.
 *
 * Opened client-side by Bootstrap (`#advanceDepositApplyModal`) with the
 * deposit's id on the trigger, and loaded lazily by public/js/modal-loader.js.
 *
 * Only the bills of the account the credit belongs to are offered, because the
 * credit is held for a homeowner account: applying it anywhere else would be
 * settling one household's bill with another's money.
 *
 * The officer chooses how much of the credit to draw: no more than the credit
 * holds and no more than the bill still stands for.
 */
class AdvanceDepositApplyModal extends AdminModalComponent
{
    const MODAL_ID = 'advanceDepositApplyModal';

    /** @var int|null Deposit the credit comes from */
    public $depositId = null;

    /** @var string The id of the bill being settled */
    public $bill = '';

    /** @var string How much of the credit is drawn */
    public $amount = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdvanceDepositApplyModal'  => 'load',
        'closeAdvanceDepositApplyModal' => 'closeModal',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    /** @var BillOptionQuery */
    protected $billQuery;

    /** @var ApplyAdvanceDeposit */
    protected $applyDeposit;

    public function boot(
        AdvanceDepositListQuery $query,
        BillOptionQuery $billQuery,
        ApplyAdvanceDeposit $applyDeposit
    ) {
        $this->query = $query;
        $this->billQuery = $billQuery;
        $this->applyDeposit = $applyDeposit;
    }

    // =====================================================================
    // Load — the shell is already on screen when this runs
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('apply', AdvanceDeposit::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->depositId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetModalState(): void
    {
        $this->depositId = null;
        $this->bill = '';
        $this->amount = '';
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('apply', AdvanceDeposit::class);

        $this->validate([
            'bill'   => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'bill.required'   => 'Choose the bill this credit settles.',
            'amount.required' => 'Enter how much of the credit to apply.',
            'amount.min'      => 'The amount must be at least 0.01.',
        ], [
            'bill'   => 'bill',
            'amount' => 'amount',
        ]);

        $deposit = $this->query->find((int) $this->depositId);
        $bill = $this->billQuery->find($this->bill);
        $amount = bcadd((string) $this->amount, '0', 2);

        if ((int) $bill->user_id !== (int) $deposit->user_id) {
            $this->addError('bill', 'That bill belongs to another homeowner.');

            return;
        }

        if (bccomp($amount, (string) $deposit->balance, 2) > 0) {
            $this->addError('amount', 'The amount is more than the credit this deposit holds.');

            return;
        }

        if (bccomp($amount, (string) $bill->balance, 2) > 0) {
            $this->addError('amount', 'The amount is more than the bill still stands for.');

            return;
        }

        $payment = $this->applyDeposit->handle($deposit, $bill, $amount, auth('web')->user());

        if (! $payment) {
            $this->emit('notify', 'warning', 'Nothing could be applied — the credit or the bill has already been settled.');

            return;
        }

        $this->emit('notify', 'success', $payment->amount_display . ' applied from the advance deposit.');
        $this->emit('advance-deposit-saved');
        $this->emit('payment-recorded');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // The deposit and its open bills are read only while the dialog is on
        // screen — the shell's first render still compiles the hidden content,
        // so it is handed an empty state until the record has been loaded.
        if (! $this->loaded) {
            return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-apply-modal', [
                'deposit'  => null,
                'bills'    => [],
                'canApply' => false,
            ]);
        }

        $deposit = $this->depositId ? $this->query->find((int) $this->depositId) : null;
        $bills = $deposit ? $this->billQuery->options($deposit->user_id) : [];

        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-apply-modal', [
            'deposit'  => $deposit,
            'bills'    => $bills,
            'canApply' => (bool) $deposit && $deposit->is_open && $bills !== [],
        ]);
    }
}
