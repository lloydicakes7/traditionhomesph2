<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Actions\Collections\MonthlyDues\DeleteMonthlyDues;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\MonthlyDueListQuery;

/**
 * Delete Monthly Due — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#dueDeleteModal`) with the due's id on the
 * trigger, and loaded lazily by public/js/modal-loader.js. A month money has
 * been received against is refused here and points the officer at Cancel
 * instead — the ledger keeps what was paid.
 */
class MonthlyDueDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'dueDeleteModal';

    /** @var int|null Due the confirmation applies to */
    public $deletingDueId = null;

    /** @var string Reference of the due the confirmation names */
    public $deletingDueLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadDueDeleteModal'  => 'load',
        'closeDueDeleteModal' => 'closeModal',
    ];

    /** @var MonthlyDueListQuery */
    protected $query;

    /** @var DeleteMonthlyDues */
    protected $deleteDues;

    public function boot(MonthlyDueListQuery $query, DeleteMonthlyDues $deleteDues)
    {
        $this->query = $query;
        $this->deleteDues = $deleteDues;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('deleteMonthlyDue', Bill::class);

        $due = $this->query->find($id);

        $this->deletingDueId = $due->id;
        $this->deletingDueLabel = $due->reference;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingDueId = null;
        $this->deletingDueLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingDueId) {
            return;
        }

        $this->authorize('deleteMonthlyDue', Bill::class);

        $deleted = $this->deleteDues->handle([$this->deletingDueId], auth('web')->user());

        if ($deleted) {
            $this->emit('notify', 'success', 'Monthly due invoice deleted.');
        } else {
            $this->emit('notify', 'warning', 'That month has payments recorded against it and cannot be deleted. Cancel it instead.');
        }

        $this->emit('due-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.monthly-dues.modals.monthly-due-delete-modal');
    }
}
