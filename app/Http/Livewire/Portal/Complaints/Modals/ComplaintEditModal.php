<?php

namespace App\Http\Livewire\Portal\Complaints\Modals;

use App\Actions\Support\Complaints\UpdateComplaint;
use App\Enums\ComplaintCategory;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\Concerns\WithComplaintRules;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Support\Complaints\ComplaintDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * "Edit Complaint" modal — the homeowner correcting a complaint the office has
 * not acted on yet.
 *
 * Opened client-side by Bootstrap (`#complaintEditModal`) with the complaint's
 * id on the trigger, and loaded lazily by public/js/modal-loader.js. Ownership
 * and the pending status are ComplaintPolicy::updateOwn's call, checked when
 * the dialog loads and again when it saves. The shared complaint rules keep the
 * fields identical to the filing form.
 */
class ComplaintEditModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount,
        WithComplaintRules;

    const MODAL_ID = 'complaintEditModal';

    /** @var int|null ID of the complaint being edited */
    public $editingComplaintId = null;

    /** @var \App\Models\Complaint|null */
    public $complaint = null;

    /** @var array Form fields */
    public $form = [
        'category'          => '',
        'other_description' => '',
        'subject'           => '',
        'description'       => '',
    ];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadComplaintEditModal'  => 'load',
        'closeComplaintEditModal' => 'closeModal',
    ];

    /** @var ComplaintDetailQuery */
    protected $detail;

    /** @var UpdateComplaint */
    protected $updateComplaint;

    public function boot(ComplaintDetailQuery $detail, UpdateComplaint $updateComplaint)
    {
        $this->detail = $detail;
        $this->updateComplaint = $updateComplaint;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return $this->complaintRules();
    }

    // =====================================================================
    // Load / close
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $complaint = $this->detail->find($id);

        $this->authorize('updateOwn', $complaint);

        $this->complaint = $complaint;
        $this->editingComplaintId = $complaint->id;
        $this->form = [
            'category'          => $complaint->category ? $complaint->category->value : '',
            'other_description' => (string) $complaint->other_description,
            'subject'           => $complaint->subject,
            'description'       => $complaint->description,
        ];

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->editingComplaintId = null;
        $this->complaint = null;
        $this->form = $this->emptyForm();

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Reactive field handling
    // =====================================================================

    /**
     * A written-out complaint type belongs only to the "Other" category.
     */
    public function updatedFormCategory(): void
    {
        if (($this->form['category'] ?? '') !== ComplaintCategory::OTHER) {
            $this->form['other_description'] = '';
        }
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded || ! $this->editingComplaintId) {
            return;
        }

        $complaint = $this->detail->find($this->editingComplaintId);

        if (! $this->account()->can('updateOwn', $complaint)) {
            $this->addError('form.subject', 'This complaint can no longer be edited: the office has already acted on it.');

            return;
        }

        $this->validate();

        try {
            $this->updateComplaint->handle($complaint, $this->form, null);
        } catch (ValidationException $exception) {
            $this->addErrorsFrom($exception);

            return;
        }

        $this->emit('notify', 'success', 'Complaint updated successfully.');
        $this->emit('complaint-edited');

        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * Put a refusal from the write beside the field it belongs to.
     */
    private function addErrorsFrom(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            $this->addError($field, $messages[0]);
        }
    }

    /**
     * A blank form, used to reset the dialog.
     *
     * @return array
     */
    private function emptyForm(): array
    {
        return [
            'category'          => '',
            'other_description' => '',
            'subject'           => '',
            'description'       => '',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.complaints.modals.complaint-edit-modal', [
            'categories' => ComplaintCategory::labels(),
        ]);
    }
}
