<?php

namespace App\Http\Livewire\Admin\Support\Violations\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Violation;
use App\Queries\Support\Violations\ViolationDetailQuery;

/**
 * "Violation Details" modal — the whole notice, read-only.
 *
 * Opens instantly through Bootstrap (`#violationViewModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadViolationViewModal`.
 * It shows what the record says and how far it has come: the member it is
 * against, what it was for, when and where it happened, the officer's own report
 * when one was filed, whether the homeowner has confirmed receiving it, and the
 * timeline from issued to acknowledged to resolved.
 *
 * Nothing is written from here. The one move a reader makes is to print the
 * notice, which is a normal route to a single-action controller because Livewire
 * cannot stream a download (see Admin\SupportController::violationNotice()).
 */
class ViolationViewModal extends AdminModalComponent
{
    /** @var int|null ID of the violation being read */
    public $viewingViolationId = null;

    /** @var \App\Models\Violation|null */
    public $violation = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadViolationViewModal'  => 'load',
        'closeViolationViewModal' => 'closeModal',
    ];

    /** @var ViolationDetailQuery */
    protected $detail;

    public function boot(ViolationDetailQuery $detail)
    {
        $this->detail = $detail;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('view', Violation::class);

        $this->violation = $this->detail->find($id);
        $this->viewingViolationId = $this->violation->id;

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->viewingViolationId = null;
        $this->violation = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.violations.modals.violation-view-modal');
    }
}
