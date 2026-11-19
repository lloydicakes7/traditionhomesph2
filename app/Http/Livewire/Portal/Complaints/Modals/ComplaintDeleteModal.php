<?php

namespace App\Http\Livewire\Portal\Complaints\Modals;

use App\Actions\Support\Complaints\DeleteComplaint;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Support\Complaints\ComplaintDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * Delete Complaint — the confirmation for a complaint the homeowner filed and
 * the office has not acted on yet.
 *
 * Opened client-side by Bootstrap (`#complaintDeleteModal`) with the
 * complaint's id on the trigger, and loaded lazily by
 * public/js/modal-loader.js. ComplaintPolicy::deleteOwn is checked as the
 * dialog loads and again when the deletion is confirmed.
 */
class ComplaintDeleteModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'complaintDeleteModal';

    /** @var int|null ID of the complaint being deleted */
    public $deletingComplaintId = null;

    /** @var array|null Summary shown in the confirmation */
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
    // Load / close
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $complaint = $this->detail->find($id);

        $this->authorize('deleteOwn', $complaint);

        $this->deletingComplaintId = $complaint->id;
        $this->deletingComplaintData = [
            'subject'  => $complaint->subject,
            'category' => $complaint->category_label,
            'status'   => $complaint->status_label,
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->deletingComplaintId = null;
        $this->deletingComplaintData = null;

        $this->resetValidation();

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

        $complaint = $this->detail->find($this->deletingComplaintId);

        if (! $this->account()->can('deleteOwn', $complaint)) {
            $this->addError('delete', 'This complaint can no longer be deleted — the office has already acted on it.');

            return;
        }

        try {
            $this->deleteComplaint->handle($complaint, null);
        } catch (ValidationException $exception) {
            $this->addError('delete', $exception->errors()['delete'][0] ?? 'This complaint can no longer be deleted.');

            return;
        }

        $this->emit('notify', 'info', 'Complaint deleted.');
        $this->emit('complaint-deleted');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.complaints.modals.complaint-delete-modal');
    }
}
