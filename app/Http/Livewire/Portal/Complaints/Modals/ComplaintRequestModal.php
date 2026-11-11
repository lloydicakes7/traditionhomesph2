<?php

namespace App\Http\Livewire\Portal\Complaints\Modals;

use App\Enums\ComplaintCategory;
use App\Http\Livewire\PortalModalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Models\Complaint;
use App\Actions\Support\Complaints\FileComplaint;
use App\Http\Livewire\Concerns\WithComplaintRules;
use Illuminate\Support\Facades\Auth;

/**
 * "File a Complaint" modal — the homeowner raising something with the office.
 *
 * Opened by native Bootstrap JS: the list's button carries `data-toggle`, and
 * `public/js/modal-loader.js` turns that into `openComplaintRequestModal`. The
 * shell is on screen at the click; the blank form is rendered on that load.
 * Emits `complaint-filed` after a complaint is submitted, which refreshes the
 * list so the homeowner sees it on their own page immediately.
 *
 * A portal submission is the same record the office works from, filed through
 * the same service the office's own form uses, with two things the portal owns:
 * the complaint is filed for the account's own resident and never for somebody
 * else, and the submitter is that resident — the office's queue shows the same
 * person the portal shows. The field rules are the service's, so both sides ask
 * for a complaint the same way.
 */
class ComplaintRequestModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'complaintRequestModal';

    /** @var array Form fields */
    public $form = [
        'category'    => '',
        'subject'     => '',
        'description' => '',
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openComplaintRequestModal'  => 'open',
        'closeComplaintRequestModal' => 'closeModal',
    ];

    /** @var FileComplaint */
    protected $fileComplaint;

    public function boot(FileComplaint $fileComplaint)
    {
        $this->fileComplaint = $fileComplaint;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return $this->complaintRules();
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    public function open(): void
    {
        $this->authorize('createOwn', Complaint::class);

        $this->form = [
            'category'    => '',
            'subject'     => '',
            'description' => '',
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the form when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->form = [
            'category'    => '',
            'subject'     => '',
            'description' => '',
        ];

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Submit
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        // Authorized again on submit, not only when the dialog opened: the
        // answer is checked where the write happens.
        $this->authorize('createOwn', Complaint::class);

        $this->validate();

        $this->fileComplaint->handle($this->form, Auth::guard('homeowner')->user());

        $this->emit('notify', 'success', 'Complaint submitted — the office will review it and post an update here.');
        $this->emit('complaint-filed');

        // Hide through Bootstrap first; the loader's hidden event then drops
        // the form (see public/js/modal-loader.js).
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.complaints.modals.complaint-request-modal', [
            'categories' => ComplaintCategory::labels(),
        ]);
    }
}
