<?php

namespace App\Http\Livewire\Portal\Requests\Modals;

use App\Actions\Support\Requests\UpdateRequest;
use App\Enums\BusinessPermitPurpose;
use App\Enums\RequestType;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\Concerns\WithRequestFilingRules;
use App\Http\Livewire\PortalModalComponent;
use App\Models\Request;
use App\Queries\Support\Requests\PortalRequestQuery;
use App\Queries\Support\Requests\RequestProfileQuery;
use Illuminate\Support\Facades\Auth;

/**
 * "Edit Request" modal — the homeowner correcting a filing the office has not
 * decided on yet.
 *
 * Opened client-side by Bootstrap (`#requestEditModal`) with the request's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Ownership and
 * the pending status are RequestPolicy::updateOwn's call, checked when the
 * dialog loads and again when it saves; UpdateRequest refuses anything that is
 * no longer pending, so an approved request cannot be rewritten by any path.
 *
 * The fields and their rules are the filing form's own, so a homeowner edits a
 * request exactly as they filed it. The actor is the homeowner, never an
 * officer, so the change is not written to the office's activity log.
 */
class RequestEditModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount,
        WithRequestFilingRules;

    const MODAL_ID = 'requestEditModal';

    /** @var int|null ID of the request being edited */
    public $editingRequestId = null;

    /** @var \App\Models\Request|null */
    public $request = null;

    /** @var array Form fields */
    public $form = [
        'type'               => '',
        'purpose'            => '',
        'business_name'      => '',
        'nature_of_business' => '',
        'hours_of_operation' => '',
        'purpose_other'      => '',
        'permit_type'        => '',
        'location'           => '',
        'scope_of_work'      => '',
        'reason'             => '',
        'addressee'          => '',
        'letter_date'        => '',
        'membership'         => [],
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestEditModal'  => 'load',
        'closeRequestEditModal' => 'closeModal',
    ];

    /** @var PortalRequestQuery */
    protected $requests;

    /** @var UpdateRequest */
    protected $updateRequest;

    /** @var RequestProfileQuery */
    protected $profiles;

    public function boot(
        PortalRequestQuery $requests,
        UpdateRequest $updateRequest,
        RequestProfileQuery $profiles
    ) {
        $this->requests = $requests;
        $this->updateRequest = $updateRequest;
        $this->profiles = $profiles;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return $this->filingRules($this->form);
    }

    // =====================================================================
    // Load / close
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $request = $this->requests->find($id);

        $this->authorize('updateOwn', $request);

        $this->request = $request;
        $this->editingRequestId = $request->id;
        $this->form = [
            'type'               => $request->type ? $request->type->value : '',
            'purpose'            => (string) $request->purpose,
            'business_name'      => (string) $request->business_name,
            'nature_of_business' => (string) $request->nature_of_business,
            'hours_of_operation' => (string) $request->hours_of_operation,
            'purpose_other'      => (string) $request->purpose_other,
            'permit_type'        => (string) $request->permit_type,
            'location'           => (string) $request->location,
            'scope_of_work'      => (string) $request->scope_of_work,
            'reason'             => (string) $request->reason,
            'addressee'          => (string) $request->addressee,
            'letter_date'        => $request->letter_date ? $request->letter_date->toDateString() : '',
            'membership'         => (array) $request->membership_data,
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->editingRequestId = null;
        $this->request = null;
        $this->form = $this->emptyForm();

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Reactive field handling
    // =====================================================================

    /**
     * The type changed: keep its value and clear every type-specific field.
     */
    public function updatedFormType(): void
    {
        $type = $this->form['type'] ?? '';

        $this->form = array_merge($this->emptyForm(), [
            'type'   => $type,
            'reason' => $this->form['reason'] ?? '',
        ]);

        $this->resetValidation();
    }

    /**
     * The purpose changed: a business permit's written-out text only belongs
     * to the "Other" purpose.
     */
    public function updatedFormPurpose(): void
    {
        if (($this->form['type'] ?? '') !== RequestType::BUSINESS_PERMIT) {
            return;
        }

        if (($this->form['purpose'] ?? '') !== BusinessPermitPurpose::OTHER) {
            $this->form['purpose_other'] = '';
        }
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded || ! $this->editingRequestId) {
            return;
        }

        $request = $this->requests->find($this->editingRequestId);

        if (! $this->account()->can('updateOwn', $request)) {
            $this->addError('form.type', 'This request has already been decided — it can no longer be edited.');

            return;
        }

        $this->validate();

        $this->updateRequest->handle($request, $this->form, null);

        $this->emit('notify', 'success', 'Request updated successfully.');
        $this->emit('request-edited');

        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $type = $this->form['type'] ? RequestType::tryFrom($this->form['type']) : null;
        $residentId = $this->request ? $this->request->resident_id : $this->account()->resident_id;

        return view('livewire.portal.requests.modals.request-edit-modal', [
            'selectedType'       => $type,
            'purposeOptions'     => $type ? $type->purposeOptions() : [],
            'membershipProfile'  => $type && $type->usesProfileRecord()
                ? $this->profiles->membershipForm($residentId)
                : null,
            'respondentUnit'     => $type && $type->hasAddressee()
                ? $this->profiles->unit($residentId)
                : null,
            'detailsInformation' => $type
                ? $this->profiles->details($residentId, $type, $this->form)
                : [],
        ]);
    }

    /**
     * A blank form, used to reset and to clear the fields a type does not carry.
     *
     * @return array
     */
    private function emptyForm(): array
    {
        return [
            'type'               => '',
            'purpose'            => '',
            'business_name'      => '',
            'nature_of_business' => '',
            'hours_of_operation' => '',
            'purpose_other'      => '',
            'permit_type'        => '',
            'location'           => '',
            'scope_of_work'      => '',
            'reason'             => '',
            'addressee'          => '',
            'letter_date'        => now()->toDateString(),
            'membership'         => [],
        ];
    }
}
