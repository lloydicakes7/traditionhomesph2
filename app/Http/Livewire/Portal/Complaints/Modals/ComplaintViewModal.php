<?php

namespace App\Http\Livewire\Portal\Complaints\Modals;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Support\Complaints\ComplaintDetailQuery;

/**
 * "Complaint Details" modal — the homeowner reading one of their own filings.
 *
 * Opened client-side by Bootstrap (`#complaintViewModal`) with the complaint's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. The record
 * is read through ComplaintPolicy::viewOwn, so another household's complaint is
 * never shown.
 */
class ComplaintViewModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'complaintViewModal';

    /** @var int|null ID of the complaint being read */
    public $viewingComplaintId = null;

    /** @var \App\Models\Complaint|null */
    public $complaint = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadComplaintViewModal'  => 'load',
        'closeComplaintViewModal' => 'closeModal',
    ];

    /** @var ComplaintDetailQuery */
    protected $detail;

    public function boot(ComplaintDetailQuery $detail)
    {
        $this->detail = $detail;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $complaint = $this->detail->find($id);

        $this->authorize('viewOwn', $complaint);

        $this->complaint = $complaint;
        $this->viewingComplaintId = $complaint->id;

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->viewingComplaintId = null;
        $this->complaint = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.complaints.modals.complaint-view-modal');
    }
}
