<?php

namespace App\Http\Livewire\Portal\Requests\Modals;

use App\Actions\Support\Requests\WithdrawRequest;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Support\Requests\PortalRequestQuery;

/**
 * Delete Request — the confirmation the portal's own Delete action opens.
 *
 * Opened client-side by Bootstrap (`#requestWithdrawModal`) with the request's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. A
 * homeowner may remove their own filing only while the office has not acted on
 * it yet: RequestPolicy::deleteOwn checks ownership and the pending status as
 * the dialog loads and again when it is confirmed.
 */
class RequestWithdrawModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'requestWithdrawModal';

    /** @var int|null Request the confirmation applies to */
    public $withdrawingRequestId = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestWithdrawModal'  => 'load',
        'closeRequestWithdrawModal' => 'closeModal',
    ];

    /** @var PortalRequestQuery */
    protected $requests;

    /** @var WithdrawRequest */
    protected $withdrawRequest;

    public function boot(PortalRequestQuery $requests, WithdrawRequest $withdrawRequest)
    {
        $this->requests = $requests;
        $this->withdrawRequest = $withdrawRequest;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $request = $this->requests->find($id);

        $this->authorize('deleteOwn', $request);

        $this->withdrawingRequestId = $request->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->withdrawingRequestId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function withdraw(): void
    {
        if (! $this->loaded || ! $this->withdrawingRequestId) {
            return;
        }

        $request = $this->requests->find($this->withdrawingRequestId);

        $this->authorize('deleteOwn', $request);

        $this->withdrawRequest->handle($request);

        $this->emit('notify', 'info', 'Request deleted.');
        $this->emit('request-withdrawn');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.requests.modals.request-withdraw-modal', [
            'request' => $this->withdrawingRequestId
                ? $this->requests->find($this->withdrawingRequestId)
                : null,
        ]);
    }
}
