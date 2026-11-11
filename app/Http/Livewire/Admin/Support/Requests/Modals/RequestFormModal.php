<?php

namespace App\Http\Livewire\Admin\Support\Requests\Modals;

use App\Enums\BusinessPermitPurpose;
use App\Enums\RequestType;
use App\Actions\Support\Requests\FileRequestForResident;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithRequestFilingRules;
use App\Models\Request;
use App\Queries\Homeowners\Residents\ResidentOptionQuery;
use App\Queries\Support\Requests\RequestProfileQuery;

/**
 * "New Request" modal — the office filing a request on a homeowner's behalf.
 *
 * The portal is where a request usually comes from, but the office takes them
 * over the counter too: a homeowner who walks in and asks for a certification,
 * a membership form or a permit. This dialog is that filing.
 *
 * Opens instantly through Bootstrap (`#requestFormModal`, triggered from the
 * list's own button with `data-toggle`); the blank form is rendered on that
 * load, when `public/js/modal-loader.js` fires `openRequestFormModal`. Emits
 * `request-saved` after the write, which refreshes the list.
 *
 * The fields shown — and the fields validated — depend on the request type the
 * officer selects, and the rule array is the service's own, so this form, the
 * portal's filing form and the edit dialog can never ask for a request
 * differently. Changing the type clears the fields the new type does not carry.
 *
 * The one thing this form has and the others do not is the homeowner: the
 * officer names the resident the request is for, which is exactly what the
 * portal takes from the signed-in account.
 */
class RequestFormModal extends AdminModalComponent
{
    use WithRequestFilingRules;

    const MODAL_ID = 'requestFormModal';

    /** @var array Form fields */
    public $form = [
        'resident_id'        => '',
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

    /** @var array<string, string> Modal open / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openRequestFormModal'  => 'openCreate',
        'closeRequestFormModal' => 'closeModal',
    ];

    /** @var ResidentOptionQuery */
    protected $residents;

    /** @var FileRequestForResident */
    protected $fileRequest;

    /** @var RequestProfileQuery */
    protected $profiles;

    public function boot(
        ResidentOptionQuery $residents,
        FileRequestForResident $fileRequest,
        RequestProfileQuery $profiles
    ) {
        $this->residents = $residents;
        $this->fileRequest = $fileRequest;
        $this->profiles = $profiles;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        // The form is handed in so the rules follow the selected type, and a
        // business permit's "Other" purpose requires its written-out text.
        return $this->filingForResidentRules($this->form);
    }

    protected function messages(): array
    {
        return [
            'form.resident_id.required' => 'Choose the homeowner this request is for.',
            'form.resident_id.exists'   => 'That homeowner is no longer on file.',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Request::class);

        $this->form = $this->emptyForm();

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the form when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
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

        $this->form = array_merge($this->emptyForm(), [
            'resident_id' => $this->form['resident_id'] ?? '',
            'type'        => $type,
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
    // Submit
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        // Authorized again on submit, not only when the dialog opened: the
        // answer is checked where the write happens.
        $this->authorize('create', Request::class);

        $this->validate();

        $request = $this->fileRequest->handle($this->form, auth('web')->user());

        $this->emit(
            'notify',
            'success',
            $request->type_label . ' request filed for ' . $request->resident_label . '.'
        );
        $this->emit('request-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $type = $this->form['type'] ? RequestType::tryFrom($this->form['type']) : null;
        $residentId = ($this->form['resident_id'] ?? '') !== '' ? (int) $this->form['resident_id'] : null;

        return view('livewire.admin.support.requests.modals.request-form-modal', [
            'residents'         => $this->residents->all(),
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
            'resident_id'        => '',
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
