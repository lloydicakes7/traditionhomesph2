<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\MonthlyDueListQuery;

/**
 * View Monthly Due Details — one property's dues for one month, read-only: how
 * the month was priced, what it comes to, and the payments recorded against it.
 *
 * Opened client-side by Bootstrap (`#dueViewModal`) with the due's id on the
 * trigger, and loaded lazily by public/js/modal-loader.js. The rate
 * configuration the month was billed under is shown from the record's own
 * snapshot rather than from the settings, so a month already billed always
 * reads as it was priced.
 */
class MonthlyDueViewModal extends AdminModalComponent
{
    /** @var int|null Due being shown */
    public $dueId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadDueViewModal'  => 'load',
        'closeDueViewModal' => 'closeModal',
    ];

    /** @var MonthlyDueListQuery */
    protected $query;

    public function boot(MonthlyDueListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when this runs
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAnyMonthlyDues', Bill::class);

        $this->dueId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->dueId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.monthly-dues.modals.monthly-due-view-modal', [
            'due' => $this->dueId ? $this->query->find($this->dueId) : null,
        ]);
    }
}
