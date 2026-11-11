<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Actions\Collections\MonthlyDues\CancelMonthlyDue;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\MonthlyDueListQuery;

/**
 * Cancel Monthly Due — the confirmation a row's Cancel action opens.
 *
 * Opened client-side by Bootstrap (`#dueCancelModal`) with the due's id on the
 * trigger, and loaded lazily by public/js/modal-loader.js. Withdrawing a month
 * stops it being collectible for that property; the payments already recorded
 * stay on the ledger, which is why this is not a delete.
 */
class MonthlyDueCancelModal extends AdminModalComponent
{
    const MODAL_ID = 'dueCancelModal';

    /** @var int|null Due the confirmation applies to */
    public $cancellingDueId = null;

    /** @var string Reference of the due the confirmation names */
    public $cancellingDueLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadDueCancelModal'  => 'load',
        'closeDueCancelModal' => 'closeModal',
    ];

    /** @var MonthlyDueListQuery */
    protected $query;

    /** @var CancelMonthlyDue */
    protected $cancelDue;

    public function boot(MonthlyDueListQuery $query, CancelMonthlyDue $cancelDue)
    {
        $this->query = $query;
        $this->cancelDue = $cancelDue;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('updateMonthlyDue', Bill::class);

        $due = $this->query->find($id);

        $this->cancellingDueId = $due->id;
        $this->cancellingDueLabel = $due->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->cancellingDueId = null;
        $this->cancellingDueLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Cancel
    // =====================================================================

    public function cancel(): void
    {
        if (! $this->loaded || ! $this->cancellingDueId) {
            return;
        }

        $this->authorize('updateMonthlyDue', Bill::class);

        $due = $this->cancelDue->handle($this->query->find($this->cancellingDueId), auth('web')->user());

        $this->emit('notify', 'success', 'Monthly due ' . $due->reference . ' cancelled.');
        $this->emit('due-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.monthly-dues.modals.monthly-due-cancel-modal');
    }
}
