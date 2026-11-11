<?php

namespace App\Http\Livewire\Admin\Support\Complaints\Modals;

use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Actions\Support\Complaints\DeleteComplaints;
use App\Models\Complaint;

/**
 * "Delete Selected Complaints" confirmation modal.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list. It uses the shared
 * `WithMassDelete` trait for the delete flow and emits `complaint-saved` on
 * completion.
 */
class ComplaintMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'complaintMassDeleteModal';

    /** @var array Ids received from the table's selection */
    public $selectedIds = [];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadComplaintMassDeleteModal'  => 'load',
        'closeComplaintMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteComplaints */
    protected $deleteComplaints;

    public function boot(DeleteComplaints $deleteComplaints)
    {
        $this->deleteComplaints = $deleteComplaints;
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Complaint::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'complaints';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteComplaints->handle($ids, auth('web')->user());
    }

    /**
     * The complaints table reloads on this event — the confirmation is not part of it.
     */
    protected function massDeleteSavedEvent(): ?string
    {
        return 'complaint-saved';
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
        return view('livewire.admin.support.complaints.modals.complaint-mass-delete-modal');
    }
}
