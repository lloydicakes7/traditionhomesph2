<?php

namespace App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals;

use App\Actions\Collections\AdvanceDeposits\RecordAdvanceDeposit;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdvanceDeposit;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;
use Illuminate\Support\Carbon;

/**
 * Add Advance Deposit — money received ahead of a bill.
 *
 * Opened client-side by Bootstrap (`#advanceDepositFormModal`) with no id, and
 * loaded lazily by public/js/modal-loader.js.
 */
class AdvanceDepositFormModal extends AdminModalComponent
{
    const MODAL_ID = 'advanceDepositFormModal';

    /** @var array<string, mixed> */
    public $form = [
        'user_id'      => '',
        'amount'       => 0,
        'deposited_at' => '',
        'notes'        => '',
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openAdvanceDepositFormModal'  => 'openCreate',
        'closeAdvanceDepositFormModal' => 'closeModal',
    ];

    /** @var AdvanceDepositListQuery */
    protected $query;

    /** @var RecordAdvanceDeposit */
    protected $recordDeposit;

    public function boot(AdvanceDepositListQuery $query, RecordAdvanceDeposit $recordDeposit)
    {
        $this->query = $query;
        $this->recordDeposit = $recordDeposit;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when this runs
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', AdvanceDeposit::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->form['deposited_at'] = Carbon::now()->format('Y-m-d');

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
        $this->form = [
            'user_id'      => '',
            'amount'       => 0,
            'deposited_at' => '',
            'notes'        => '',
        ];
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('create', AdvanceDeposit::class);

        $this->validate([
            'form.user_id'      => ['required', 'exists:users,id'],
            'form.amount'       => ['required', 'numeric', 'min:0.01'],
            'form.deposited_at'             => ['required', 'date'],
            'form.notes'                    => ['nullable', 'string', 'max:1000'],
        ], [
            'form.user_id.required'      => 'Choose the homeowner the credit is held for.',
            'form.amount.required'       => 'Enter the amount received.',
            'form.amount.min'            => 'The amount must be more than zero.',
            'form.deposited_at.required' => 'Enter when the money was received.',
        ], [
            'form.user_id'      => 'homeowner',
            'form.amount'       => 'amount',
            'form.deposited_at'             => 'date received',
            'form.notes'                    => 'notes',
        ]);

        $deposit = $this->recordDeposit->handle([
            'user_id'      => $this->form['user_id'],
            'amount'       => $this->form['amount'],
            'deposited_at' => $this->form['deposited_at'],
            'notes'        => $this->form['notes'] ?: null,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Advance deposit recorded — ' . $deposit->balance_display . ' of credit held.');
        $this->emit('advance-deposit-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        if (! $this->loaded) {
            return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-form-modal', [
                'users' => [],
            ]);
        }

        return view('livewire.admin.collections.advance-deposits.modals.advance-deposit-form-modal', [
            'users' => $this->query->holderOptions(),
        ]);
    }
}
