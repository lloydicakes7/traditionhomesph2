<?php

namespace App\Http\Livewire\Admin\Support\Violations\Modals;

use App\Enums\ViolationType;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Violation;
use App\Actions\Support\Violations\IssueViolation;
use App\Actions\Support\Violations\UpdateViolation;
use App\Http\Livewire\Concerns\WithViolationRules;
use App\Queries\Homeowners\Residents\ResidentOptionQuery;
use App\Queries\Support\Violations\ViolationDetailQuery;

/**
 * "Issue / Edit Violation" modal — one form for both flows.
 *
 * Opens instantly through Bootstrap (`#violationFormModal`, triggered with
 * `data-toggle`, plus `data-id` when editing); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires `loadViolationFormModal`.
 * Emits `violation-saved` after a successful save, which refreshes the list.
 *
 * The form asks for the two things an officer is looking at: the member, and
 * the violation's own facts — what it was for, when and where it happened, and
 * what the office saw. The office report is the optional part of it: an officer
 * files one when there is something worth writing down for the association's
 * own records, and leaves it blank when there is not.
 *
 * The rules are the support module's own, so this form cannot ask for a
 * violation differently from the dialogs beside it
 * about what a violation needs, and the write itself goes through the service —
 * issuing is what stamps the record as issued and tells the homeowner.
 */
class ViolationFormModal extends AdminModalComponent
{
    use WithViolationRules;

    /** @var bool Whether editing an existing violation */
    public $isEditing = false;

    /** @var int|null ID of the violation being edited */
    public $editingViolationId = null;

    /** @var array Form fields */
    public $form = [
        'resident_id'    => '',
        'violation_type' => '',
        'occurred_on'    => '',
        'occurred_time'  => '',
        'location'       => '',
        'description'    => '',
        'office_report'  => '',
    ];

    /** @var array<string, string> Modal open / load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openViolationFormModal'  => 'openCreate',
        'loadViolationFormModal'  => 'openEdit',
        'closeViolationFormModal' => 'closeModal',
    ];

    /** @var ResidentOptionQuery */
    protected $residents;

    /** @var ViolationDetailQuery */
    protected $detail;

    /** @var IssueViolation */
    protected $issueViolation;

    /** @var UpdateViolation */
    protected $updateViolation;

    public function boot(
        ResidentOptionQuery $residents,
        ViolationDetailQuery $detail,
        IssueViolation $issueViolation,
        UpdateViolation $updateViolation
    ) {
        $this->residents = $residents;
        $this->detail = $detail;
        $this->issueViolation = $issueViolation;
        $this->updateViolation = $updateViolation;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return $this->violationRules();
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Violation::class);

        $this->resetForm();
        $this->resetValidation();

        $this->form['occurred_on'] = now()->toDateString();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Violation::class);

        $violation = $this->detail->find($id);

        $this->resetForm();
        $this->resetValidation();

        $this->isEditing = true;
        $this->editingViolationId = $violation->id;
        $this->form = [
            'resident_id'    => (string) $violation->resident_id,
            'violation_type' => $violation->violation_type ? $violation->violation_type->value : '',
            'occurred_on'    => $violation->occurred_on ? $violation->occurred_on->format('Y-m-d') : '',
            'occurred_time'  => $violation->occurred_time ? substr((string) $violation->occurred_time, 0, 5) : '',
            'location'       => $violation->location,
            'description'    => $violation->description,
            'office_report'  => $violation->office_report ?? '',
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

        $this->authorize($this->isEditing ? 'update' : 'create', Violation::class);
        $this->validate();

        $by = auth('web')->user();

        if ($this->isEditing) {
            $this->updateViolation->handle($this->detail->find($this->editingViolationId), $this->form, $by);
        } else {
            $this->issueViolation->handle($this->form, $by);
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Violation updated successfully.'
                : 'Violation issued — it is now on the homeowner\'s record.');

        $this->emit('violation-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'violationFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingViolationId = null;
        $this->form = [
            'resident_id'    => '',
            'violation_type' => '',
            'occurred_on'    => '',
            'occurred_time'  => '',
            'location'       => '',
            'description'    => '',
            'office_report'  => '',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        // The member picker is a list of every resident on file, so it is only
        // built while the form has loaded — not for a closed shell.
        return view('livewire.admin.support.violations.modals.violation-form-modal', [
            'members' => $this->loaded ? $this->residents->all() : [],
            'types'   => ViolationType::labels(),
        ]);
    }
}
