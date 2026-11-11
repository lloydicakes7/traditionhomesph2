<?php

namespace App\Http\Livewire\Admin\Support\Complaints\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Actions\Support\Complaints\DeleteComplaint;
use App\Models\Complaint;
use App\Queries\Support\Complaints\ComplaintDetailQuery;

/**
 * "Delete Complaint" confirmation modal — shows which complaint is being
 * removed and who filed it.
 *
 * Opens instantly through Bootstrap (`#complaintDeleteModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadComplaintDeleteModal`.
 * Emits `complaint-saved` after the record is removed, which refreshes the list.
 */
class ComplaintDeleteModal extends AdminModalComponent
{
    /** @var int|null ID of the complaint being deleted */
    public $deletingComplaintId = null;

    /** @var array|null Summary of the complaint shown in the modal */
    public $deletingComplaintData = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadComplaintDeleteModal'  => 'load',
        'closeComplaintDeleteModal' => 'closeModal',
    ];

    /** @var ComplaintDetailQuery */
    protected $detail;

    /** @var DeleteComplaint */
    protected $deleteComplaint;

    public function boot(ComplaintDetailQuery $detail, DeleteComplaint $deleteComplaint)
    {
        $this->detail = $detail;
        $this->deleteComplaint = $deleteComplaint;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Complaint::class);

        $complaint = $this->detail->find($id);

        $this->deletingComplaintId = $complaint->id;
        $this->deletingComplaintData = [
            'subject'   => $complaint->subject,
            'category'  => $complaint->category_label,
            'submitter' => $complaint->submitter_label,
            'status'    => $complaint->status_label,
        ];

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->deletingComplaintId = null;
        $this->deletingComplaintData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingComplaintId) {
            return;
        }

        $this->authorize('delete', Complaint::class);
        $this->deleteComplaint->handle($this->detail->find($this->deletingComplaintId), auth('web')->user());

        $this->emit('notify', 'success', 'Complaint deleted successfully.');
        $this->emit('complaint-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'complaintDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.complaints.modals.complaint-delete-modal');
    }
}
