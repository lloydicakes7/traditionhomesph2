<?php

namespace App\Http\Livewire\Portal\Requests\Modals;

use App\Enums\BusinessPermitPurpose;
use App\Enums\RequestType;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Models\Request;
use App\Actions\Support\Requests\FileRequest;
use App\Http\Livewire\Concerns\WithRequestFilingRules;
use App\Queries\Support\Requests\RequestProfileQuery;
use Illuminate\Support\Facades\Auth;

/**
 * "File a Request" modal — the homeowner asking the office to issue a document.
 *
 * Opened by native Bootstrap JS: the list's button carries `data-toggle`, and
 * `public/js/modal-loader.js` turns that into `openRequestFileModal`. The shell
 * is on screen at the click; the blank form is rendered on that load. Emits
 * `request-filed` after a request is submitted, which refreshes the list so the
 * homeowner sees it on their own page immediately.
 *
 * The fields shown — and the fields validated — depend on the request type the
 * homeowner selects. The rule array is the support module's own, built from the
 * selected type, so an irrelevant field is never asked for or validated.
 * Changing the type clears the fields the new type does not carry.
 *
 * A portal submission is filed under the account's own resident, taken from the
 * account and never from the form — a homeowner cannot file under another
 * household by manipulating it, and the homeowner's name is never typed in.
 *
 * The lifecycle is the panel's lazy-modal one: the shell is always on screen,
 * and the form is dropped when Bootstrap reports the dialog closed — never in
 * the same round trip that asks it to hide.
 */
class RequestFileModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount,
        WithRequestFilingRules;

    const MODAL_ID = 'requestFileModal';

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
        'openRequestFileModal'  => 'open',
        'closeRequestFileModal' => 'closeModal',
    ];

    /** @var FileRequest */
    protected $fileRequest;

    /** @var RequestProfileQuery */
    protected $profiles;

    public function boot(FileRequest $fileRequest, RequestProfileQuery $profiles)
    {
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
        return $this->filingRules($this->form);
    }

    // =====================================================================
    // Reactive field handling
    // =====================================================================

    /**
     * The type changed: keep its value and clear every type-specific field, so
     * nothing from a previous type is submitted or validated.
     */
    public function updatedFormType(): void
    {
        $type = $this->form['type'] ?? '';

        $this->form = array_merge($this->emptyForm(), ['type' => $type]);

        $this->resetValidation();
    }

    /**
     * The purpose changed: a business permit's written-out text only belongs to
     * the "Other" purpose.
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
    // Open / close
    // =====================================================================

    public function open(): void
    {
        $this->authorize('createOwn', Request::class);

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
    // Submit
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        // Authorized again on submit, not only when the dialog opened: the
        // answer is checked where the write happens.
        $this->authorize('createOwn', Request::class);

        $this->validate();

        $this->fileRequest->handle($this->form, Auth::guard('homeowner')->user());

        $this->emit('notify', 'success', 'Request submitted — the office will review it and post the outcome here.');
        $this->emit('request-filed');

        // Hide through Bootstrap first; the loader's hidden event then drops
        // the form (see public/js/modal-loader.js).
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $type = $this->form['type'] ? RequestType::tryFrom($this->form['type']) : null;
        $residentId = $this->account()->resident_id;

        return view('livewire.portal.requests.modals.request-file-modal', [
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
