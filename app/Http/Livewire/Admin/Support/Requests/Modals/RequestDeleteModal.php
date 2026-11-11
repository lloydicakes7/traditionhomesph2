<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Actions\Support\Requests\DeleteRequest;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;

/**
 * "Delete Request" confirmation modal — shows what was asked for and who asked.
 *
 * Opens instantly through Bootstrap (`#requestDeleteModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadRequestDeleteModal`.
 * Emits `request-saved` after the record is removed, which refreshes the list.
 *
 * The dialog says plainly that deleting is for a request filed in error — the
 * office's answer to one it acted on is part of the submitter's record either
 * way.
 */
class RequestDeleteModal extends AdminModalComponent
{
    /** @var int|null ID of the request being deleted */
    public $deletingRequestId = null;

    /** @var array|null Summary of the request shown in the modal */
    public $deletingRequestData = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestDeleteModal'  => 'load',
        'closeRequestDeleteModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var DeleteRequest */
    protected $deleteRequest;

    public function boot(RequestDetailQuery $detail, DeleteRequest $deleteRequest)
    {
        $this->detail = $detail;
        $this->deleteRequest = $deleteRequest;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Request::class);

        $request = $this->detail->find($id);

        $this->deletingRequestId = $request->id;
        $this->deletingRequestData = [
            'type'     => $request->type_label,
            'resident' => $request->resident_label,
            'summary'  => $request->summary,
            'status'   => $request->status_label,
        ];

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->deletingRequestId = null;
        $this->deletingRequestData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingRequestId) {
            return;
        }

        $this->authorize('delete', Request::class);
        $this->deleteRequest->handle($this->detail->find($this->deletingRequestId), auth('web')->user());

        $this->emit('notify', 'success', 'Request deleted successfully.');
        $this->emit('request-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'requestDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-delete-modal');
    }
}
