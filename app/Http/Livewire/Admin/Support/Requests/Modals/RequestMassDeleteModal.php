<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Http\Livewire\Concerns\WithMassDelete;
use App\Actions\Support\Requests\DeleteRequests;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Request;

/**
 * "Delete Selected Requests" confirmation modal.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list. It uses the shared
 * `WithMassDelete` trait for the delete flow and emits `request-saved` on
 * completion.
 */
class RequestMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'requestMassDeleteModal';

    /** @var array Ids received from the table's selection */
    public $selectedIds = [];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestMassDeleteModal'  => 'load',
        'closeRequestMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteRequests */
    protected $deleteRequests;

    public function boot(DeleteRequests $deleteRequests)
    {
        $this->deleteRequests = $deleteRequests;
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Request::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'requests';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteRequests->handle($ids, auth('web')->user());
    }

    /**
     * The requests table reloads on this event — the confirmation is not part of it.
     */
    protected function massDeleteSavedEvent(): ?string
    {
        return 'request-saved';
    }

    /**
     * Drop the selection when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-mass-delete-modal');
    }
}
