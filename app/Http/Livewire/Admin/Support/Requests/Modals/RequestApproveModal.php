<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Actions\Support\Requests\ApproveRequest;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * "Approve Request" modal — the office accepting a request.
 *
 * Opens instantly through Bootstrap (`#requestApproveModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadRequestApproveModal`.
 * Emits `request-saved` after the write, which refreshes the list — the row's
 * actions then offer Print, and the submitter sees the approval on their own
 * page.
 *
 * Only a pending request can be approved: the service refuses anything else,
 * and the refusal lands beside the remarks field so the dialog shows it rather
 * than a success the write did not earn.
 */
class RequestApproveModal extends AdminModalComponent
{
    const MODAL_ID = 'requestApproveModal';

    /** @var int|null ID of the request being approved */
    public $approvingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var string Optional note on the approval */
    public $remarks = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestApproveModal'  => 'load',
        'closeRequestApproveModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var ApproveRequest */
    protected $approveRequest;

    public function boot(RequestDetailQuery $detail, ApproveRequest $approveRequest)
    {
        $this->detail = $detail;
        $this->approveRequest = $approveRequest;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:2000'],
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

        $this->authorize('approve', Request::class);

        $request = $this->detail->find($id);

        $this->request = $request;
        $this->approvingRequestId = $request->id;
        $this->remarks = '';

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->approvingRequestId = null;
        $this->request = null;
        $this->remarks = '';

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Approve
    // =====================================================================

    public function approve(): void
    {
        if (! $this->loaded || ! $this->approvingRequestId) {
            return;
        }

        $this->authorize('approve', Request::class);
        $this->validate();

        // A refusal from the service is a real failure, not a success: it is a
        // ValidationException, which lands beside the remarks field and leaves
        // the dialog open.
        try {
            $this->approveRequest->handle(
                $this->detail->find($this->approvingRequestId),
                ['remarks' => $this->remarks],
                auth('web')->user()
            );
        } catch (ValidationException $e) {
            $this->addError('remarks', $e->errors()['remarks'][0] ?? 'This request can no longer be approved.');

            return;
        }

        $this->emit('notify', 'success', 'Request approved — the document can now be printed, and its Print action is available.');
        $this->emit('request-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-approve-modal');
    }
}
