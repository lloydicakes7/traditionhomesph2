<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Actions\Support\Requests\CancelRequest;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * "Cancel Request" modal — the office refusing a request, and saying why.
 *
 * Opens instantly through Bootstrap (`#requestCancelModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadRequestCancelModal`.
 * Emits `request-saved` after the write, which refreshes the list and puts the
 * reason in front of the submitter on their own page.
 *
 * The reason is required — a cancellation that does not say why is not an
 * answer — which is the difference between this dialog and the approve one
 * beside it. Only a pending request can be cancelled: the service refuses
 * anything else, and the refusal lands beside the remarks field.
 */
class RequestCancelModal extends AdminModalComponent
{
    const MODAL_ID = 'requestCancelModal';

    /** @var int|null ID of the request being cancelled */
    public $cancellingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var string Why the request is cancelled — required */
    public $remarks = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestCancelModal'  => 'load',
        'closeRequestCancelModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var CancelRequest */
    protected $cancelRequest;

    public function boot(RequestDetailQuery $detail, CancelRequest $cancelRequest)
    {
        $this->detail = $detail;
        $this->cancelRequest = $cancelRequest;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'remarks' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'remarks.required' => 'Say why the request is being cancelled — the homeowner reads this.',
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

        $this->authorize('cancel', Request::class);

        $request = $this->detail->find($id);

        $this->request = $request;
        $this->cancellingRequestId = $request->id;
        $this->remarks = '';

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->cancellingRequestId = null;
        $this->request = null;
        $this->remarks = '';

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Cancel
    // =====================================================================

    public function cancel(): void
    {
        if (! $this->loaded || ! $this->cancellingRequestId) {
            return;
        }

        $this->authorize('cancel', Request::class);
        $this->validate();

        // A refusal from the service is a real failure, not a success: it is a
        // ValidationException, which lands beside the remarks field and leaves
        // the dialog open.
        try {
            $this->cancelRequest->handle(
                $this->detail->find($this->cancellingRequestId),
                ['remarks' => $this->remarks],
                auth('web')->user()
            );
        } catch (ValidationException $e) {
            $this->addError('remarks', $e->errors()['remarks'][0] ?? 'This request can no longer be cancelled.');

            return;
        }

        $this->emit('notify', 'success', 'Request cancelled — the reason is on the homeowner\'s own requests page.');
        $this->emit('request-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-cancel-modal');
    }
}
