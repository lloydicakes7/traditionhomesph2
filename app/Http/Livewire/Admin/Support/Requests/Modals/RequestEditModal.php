<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Enums\BusinessPermitPurpose;
use App\Enums\RequestType;
use App\Actions\Support\Requests\UpdateRequest;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithRequestFilingRules;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;
use App\Queries\Support\Requests\RequestProfileQuery;

/**
 * "Edit Request" modal — the office correcting what a request says.
 *
 * Opens instantly through Bootstrap (`#requestEditModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadRequestEditModal`.
 * Emits `request-saved` after the change, which refreshes the list.
 *
 * The fields shown — and the fields validated — depend on the request type the
 * form selects. The rule array is the service's own, built from the selected
 * type, so an irrelevant hidden field is never required and a required one is
 * never missed. Changing the type clears the fields the new type does not
 * carry, so a request never keeps values from a type it no longer is.
 *
 * Ownership is not editable: the request stays with the resident who filed it.
 */
class RequestEditModal extends AdminModalComponent
{
    use WithRequestFilingRules;

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
        'addressee'          => '',
        'letter_date'        => '',
        'membership'         => [],
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadRequestEditModal'  => 'load',
        'closeRequestEditModal' => 'closeModal',
    ];

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var UpdateRequest */
    protected $updateRequest;

    /** @var RequestProfileQuery */
    protected $profiles;

    public function boot(
        RequestDetailQuery $detail,
        UpdateRequest $updateRequest,
        RequestProfileQuery $profiles
    ) {
        $this->detail = $detail;
        $this->updateRequest = $updateRequest;
        $this->profiles = $profiles;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        // The form is handed in so the rules follow the selected type, and a
        // business permit's "Other" purpose requires its written-out text.
        return $this->filingRules($this->form);
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Request::class);

        $request = $this->detail->find($id);

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
     * The type changed: keep its value and clear every type-specific field, so
     * nothing from the previous type is submitted or validated.
     */
    public function updatedFormType(): void
    {
        $type = $this->form['type'] ?? '';

        $this->form = array_merge($this->emptyForm(), ['type' => $type]);

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

        $this->authorize('update', Request::class);
        $this->validate();

        $this->updateRequest->handle($this->detail->find($this->editingRequestId), $this->form, auth('web')->user());

        $this->emit('notify', 'success', 'Request updated successfully.');
        $this->emit('request-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'requestEditModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $type = $this->form['type'] ? RequestType::tryFrom($this->form['type']) : null;
        $residentId = $this->request ? $this->request->resident_id : null;

        return view('livewire.admin.support.requests.modals.request-edit-modal', [
            'selectedType'      => $type,
            'purposeOptions'    => $type ? $type->purposeOptions() : [],
            'membershipProfile' => $type && $type->usesProfileRecord()
                ? $this->profiles->membershipForm($residentId)
                : null,
            'respondentUnit'    => $type && $type->hasAddressee()
                ? $this->profiles->unit($residentId)
                : null,
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
            'addressee'          => '',
            'letter_date'        => now()->toDateString(),
            'membership'         => [],
        ];
    }
}
