<?php

namespace App\Http\Livewire\Portal\Violations\Modals;

use App\Http\Livewire\PortalModalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Queries\Support\Violations\ViolationDetailQuery;

/**
 * "Notice of Violation" modal — the homeowner reading the whole notice.
 *
 * Opened by native Bootstrap JS: the list's button carries `data-toggle` and
 * `data-id`, and `public/js/modal-loader.js` turns that into
 * `loadViolationViewModal`. The shell is on screen at the click; the record is
 * fetched on that load. It shows what the violation says, when and where it
 * happened, where it stands, whether the notice has been acknowledged, and the
 * timeline behind it.
 *
 * Nothing is written from here, and the record is resolved through the signed-in
 * account rather than by id alone — a homeowner cannot open another household's
 * notice even by guessing its id, which is the ViolationPolicy's own rule
 * applied as the read.
 */
class ViolationViewModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'violationViewModal';

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
    // Open / close
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $violation = $this->detail->find((int) $id);

        $this->authorize('viewOwn', $violation);

        $this->violation = $violation;
        $this->viewingViolationId = $violation->id;

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
        return view('livewire.portal.violations.modals.violation-view-modal');
    }
}
