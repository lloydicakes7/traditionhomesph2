<?php

namespace App\Http\Livewire\Admin\Accounting\Transfers\Modals;

use App\Actions\Accounting\Transfers\DeleteTransfer;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\AccountTransfer;
use App\Queries\Accounting\TransferListQuery;

/**
 * Remove Transfer — the confirmation a row's Remove action opens.
 *
 * Opened client-side by Bootstrap (`#transferDeleteModal`) with the transfer's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. A transfer
 * is never dropped from the books: removing it voids the posting behind it, so
 * the ledger keeps saying what was moved — which is the reasoning the dialog
 * states.
 */
class TransferDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'transferDeleteModal';

    /** @var int|null Transfer the confirmation applies to */
    public $deletingTransferId = null;

    /** @var string Reference of the transfer the confirmation names */
    public $deletingTransferLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadTransferDeleteModal'  => 'load',
        'closeTransferDeleteModal' => 'closeModal',
    ];

    /** @var TransferListQuery */
    protected $transfers;

    public function boot(TransferListQuery $transfers)
    {
        $this->transfers = $transfers;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', AccountTransfer::class);

        $transfer = $this->transfers->find($id);

        $this->deletingTransferId = $transfer->id;
        $this->deletingTransferLabel = $transfer->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingTransferId = null;
        $this->deletingTransferLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingTransferId) {
            return;
        }

        $this->authorize('delete', AccountTransfer::class);

        app(DeleteTransfer::class)->handle(
            $this->transfers->find($this->deletingTransferId),
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Transfer removed — the posting behind it is voided, not dropped.');
        $this->emit('transfer-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.transfers.modals.transfer-delete-modal');
    }
}
