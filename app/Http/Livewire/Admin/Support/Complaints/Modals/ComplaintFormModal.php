<?php

namespace App\Http\Livewire\Admin\Support\Complaints\Modals;

use App\Enums\ComplaintCategory;
use App\Actions\Support\Complaints\RecordComplaint;
use App\Actions\Support\Complaints\UpdateComplaint;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithComplaintRules;
use App\Models\Complaint;
use App\Queries\Homeowners\Residents\ResidentOptionQuery;
use App\Queries\Support\Complaints\ComplaintDetailQuery;

/**
 * "File / Edit Complaint" modal — the office recording a complaint that was
 * reported to it, and correcting one it recorded.
 *
 * Opens instantly through Bootstrap (`#complaintFormModal`, triggered with
 * `data-toggle`, plus `data-id` when editing); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadComplaintFormModal`.
 * Emits `complaint-saved` after a successful save, which refreshes the list.
 *
 * A complaint filed here names the resident it concerns and the officer who
 * took it down; a complaint a homeowner files from the portal names them
 * instead. Either way it is one record, on the same list, answered the same way
 * — one record, on the same list, answered the same way.
 *
 * The rules are the support module's own, so this form cannot ask for a
 * complaint differently from the portal's own.
 */
class ComplaintFormModal extends AdminModalComponent
{
    use WithComplaintRules;

    /** @var bool Whether editing an existing complaint */
    public $isEditing = false;

    /** @var int|null ID of the complaint being edited */
    public $editingComplaintId = null;

    /** @var array Form fields */
    public $form = [
        'resident_id' => '',
        'category'    => '',
        'subject'     => '',
        'description' => '',
    ];

    /** @var array<string, string> Modal open / load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openComplaintFormModal'  => 'openCreate',
        'loadComplaintFormModal'  => 'openEdit',
        'closeComplaintFormModal' => 'closeModal',
    ];

    /** @var ResidentOptionQuery */
    protected $residents;

    /** @var ComplaintDetailQuery */
    protected $detail;

    /** @var RecordComplaint */
    protected $recordComplaint;

    /** @var UpdateComplaint */
    protected $updateComplaint;

    public function boot(
        ResidentOptionQuery $residents,
        ComplaintDetailQuery $detail,
        RecordComplaint $recordComplaint,
        UpdateComplaint $updateComplaint
    ) {
        $this->residents = $residents;
        $this->detail = $detail;
        $this->recordComplaint = $recordComplaint;
        $this->updateComplaint = $updateComplaint;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return $this->complaintAdminRules();
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Complaint::class);

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Complaint::class);

        $complaint = $this->detail->find($id);

        $this->resetForm();
        $this->resetValidation();

        $this->isEditing = true;
        $this->editingComplaintId = $complaint->id;
        $this->form = [
            'resident_id' => (string) $complaint->resident_id,
            'category'    => $complaint->category ? $complaint->category->value : '',
            'subject'     => $complaint->subject,
            'description' => $complaint->description,
        ];

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->resetForm();
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Complaint::class);
        $this->validate();

        $by = auth('web')->user();

        if ($this->isEditing) {
            $this->updateComplaint->handle($this->detail->find($this->editingComplaintId), $this->form, $by);
        } else {
            $this->recordComplaint->handle($this->form, $by);
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Complaint updated successfully.'
                : 'Complaint filed — it is now on the complaints queue.');

        $this->emit('complaint-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'complaintFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingComplaintId = null;
        $this->form = [
            'resident_id' => '',
            'category'    => '',
            'subject'     => '',
            'description' => '',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.complaints.modals.complaint-form-modal', [
            'members'    => $this->loaded ? $this->residents->all() : [],
            'categories' => ComplaintCategory::labels(),
        ]);
    }
}
