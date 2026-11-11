<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Actions\Support\Requests\CompleteRequest;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * "Complete Request" modal — the office closing an approved request once its
 * document has been handed over.
 *
 * Opened client-side by Bootstrap (`#requestCompleteModal`) with the request's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. Only an
 * approved request can be completed, which the dialog asks again as it loads
 * and the Action refuses if the page is stale.
 */
class RequestCompleteModal extends AdminModalComponent
{
    const MODAL_ID = 'requestCompleteModal';

    /** @var int|null ID of the request being completed */
    public $completingRequestId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestCompleteModal'  => 'load',
        'closeRequestCompleteModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var CompleteRequest */
    protected $completeRequest;

    public function boot(RequestDetailQuery $detail, CompleteRequest $completeRequest)
    {
        $this->detail = $detail;
        $this->completeRequest = $completeRequest;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('complete', Request::class);

        $request = $this->detail->find($id);

        if (! $request->is_approved) {
            $this->emit('notify', 'warning', 'That move no longer applies to this request.');
            $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);

            return;
        }

        $this->completingRequestId = $request->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->completingRequestId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Complete
    // =====================================================================

    public function complete(): void
    {
        if (! $this->loaded || ! $this->completingRequestId) {
            return;
        }

        $this->authorize('complete', Request::class);

        try {
            $this->completeRequest->handle(
                $this->detail->find($this->completingRequestId),
                auth('web')->user()
            );
        } catch (ValidationException $e) {
            $this->emit('notify', 'error', $e->errors()['request'][0] ?? 'This request can no longer be completed.');

            return;
        }

        $this->emit('notify', 'success', 'Request completed.');
        $this->emit('request-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.requests.modals.request-complete-modal', [
            'request' => $this->completingRequestId
                ? $this->detail->find($this->completingRequestId)
                : null,
        ]);
    }
}
