<?php

namespace App\Http\Livewire\Admin\Support\Complaints\Modals;

use App\Enums\ComplaintStatus;
use App\Http\Livewire\AdminModalComponent;
use App\Actions\Support\Complaints\RespondToComplaint;
use App\Models\Complaint;
use App\Queries\Support\Complaints\ComplaintDetailQuery;
use Illuminate\Validation\Rule;

/**
 * "Respond to Complaint" modal — the office's answer and the status it moves
 * the complaint to, written as one act.
 *
 * Opens instantly through Bootstrap (`#complaintRespondModal`, triggered from
 * the row's actions with `data-toggle` + `data-id`); the record is fetched
 * lazily afterwards, when `public/js/modal-loader.js` fires
 * `loadComplaintRespondModal`. Emits `complaint-saved` after the response is
 * saved, which refreshes the list and is what puts the update in front of the
 * submitter on their own page.
 *
 * The response is required and the status is chosen with it on purpose: a
 * complaint never reads "resolved" without the answer it was resolved with.
 * A closed complaint takes no further response (ComplaintStatus::canBeRespondedTo()),
 * so the dialog says so instead of offering a form that would be refused.
 */
class ComplaintRespondModal extends AdminModalComponent
{
    /** @var int|null ID of the complaint being answered */
    public $respondingComplaintId = null;

    /** @var \App\Models\Complaint|null */
    public $complaint = null;

    /** @var string Status the complaint is moved to */
    public $responseStatus = ComplaintStatus::IN_PROGRESS;

    /** @var string What the office found or did */
    public $response = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadComplaintRespondModal'  => 'load',
        'closeComplaintRespondModal' => 'closeModal',
    ];

    /** @var ComplaintDetailQuery */
    protected $detail;

    /** @var RespondToComplaint */
    protected $respond;

    public function boot(ComplaintDetailQuery $detail, RespondToComplaint $respond)
    {
        $this->detail = $detail;
        $this->respond = $respond;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'responseStatus' => ['required', Rule::in(ComplaintStatus::values())],
            'response'       => ['required', 'string', 'max:2000'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('respond', Complaint::class);

        $complaint = $this->detail->find($id);

        $this->complaint = $complaint;
        $this->respondingComplaintId = $complaint->id;
        $this->response = $complaint->response ?? '';
        $this->responseStatus = $complaint->status ? $complaint->status->value : ComplaintStatus::PENDING;

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->respondingComplaintId = null;
        $this->complaint = null;
        $this->response = '';
        $this->responseStatus = ComplaintStatus::IN_PROGRESS;

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded || ! $this->respondingComplaintId) {
            return;
        }

        $this->authorize('respond', Complaint::class);
        $this->validate();

        // A refusal from the action is a real failure, not a success: it is a
        // ValidationException, which lands beside the status field and leaves
        // the dialog open — no toast, no close, nothing hidden.
        $this->respond->handle(
            $this->detail->find($this->respondingComplaintId),
            ['response' => $this->response, 'responseStatus' => $this->responseStatus],
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Response saved — the submitter sees the update on their own complaints page.');
        $this->emit('complaint-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'complaintRespondModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.complaints.modals.complaint-respond-modal', [
            'statuses' => ComplaintStatus::labels(),
        ]);
    }
}
