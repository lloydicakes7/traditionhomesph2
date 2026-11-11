<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;

/**
 * "Request Details" modal — the whole record, read-only.
 *
 * Opens instantly through Bootstrap (`#requestViewModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadRequestViewModal`. It
 * shows who filed it, what is being asked for and why, what it says, where it
 * stands, and the office's remarks once it has acted.
 *
 * Nothing is written from here: an officer who may act on the request does so
 * through the process dialog, so a reader without that permission can still be
 * given the full record.
 */
class RequestViewModal extends AdminModalComponent
{
    /** @var int|null ID of the request being read */
    public $viewingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestViewModal'  => 'load',
        'closeRequestViewModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    public function boot(RequestDetailQuery $detail)
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

        $this->authorize('view', Request::class);

        $this->request = $this->detail->find($id);
        $this->viewingRequestId = $this->request->id;

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
        return view('livewire.admin.support.requests.modals.request-view-modal');
    }
}
