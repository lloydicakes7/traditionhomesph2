<?php

namespace App\Http\Livewire\Portal\Requests\Modals;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Models\Request;
use App\Queries\Support\Requests\PortalRequestQuery;

/**
 * "Request Details" modal — the homeowner reading one of their own requests.
 *
 * Opened client-side by Bootstrap (`#requestViewModal`) with the request's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. The record is
 * read through the signed-in account's own resident: another household's
 * request is never shown, and RequestPolicy::viewOwn is checked again as the
 * record loads.
 */
class RequestViewModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'requestViewModal';

    /** @var int|null ID of the request being read */
    public $viewingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestViewModal'  => 'load',
        'closeRequestViewModal' => 'closeModal',
    ];

    /** @var PortalRequestQuery */
    protected $requests;

    public function boot(PortalRequestQuery $requests)
    {
        $this->requests = $requests;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $request = $this->requests->find($id);

        $this->authorize('viewOwn', $request);

        $this->request = $request;
        $this->viewingRequestId = $request->id;

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->viewingRequestId = null;
        $this->request = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.requests.modals.request-view-modal');
    }
}
