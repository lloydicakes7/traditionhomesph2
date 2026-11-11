<?php

namespace App\Http\Livewire\Admin\Support\Violations\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Violation;
use App\Actions\Support\Violations\DeleteViolation;
use App\Queries\Support\Violations\ViolationDetailQuery;

/**
 * "Delete Violation" confirmation modal — shows which violation is being
 * removed and who it is against.
 *
 * Opens instantly through Bootstrap (`#violationDeleteModal`, triggered from the
 * row's actions with `data-toggle` + `data-id`); the record is fetched lazily
 * afterwards, when `public/js/modal-loader.js` fires
 * `loadViolationDeleteModal`. Emits `violation-saved` after the record is
 * removed, which refreshes the list.
 *
 * The dialog says plainly that resolving is how a violation normally leaves a
 * member's history, and that deleting is for one issued in error — so an
 * officer is not tempted to delete their way to a clean record, which is what
 * the status is for.
 */
class ViolationDeleteModal extends AdminModalComponent
{
    /** @var int|null ID of the violation being deleted */
    public $deletingViolationId = null;

    /** @var array|null Summary of the violation shown in the modal */
    public $deletingViolationData = null;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadViolationDeleteModal'  => 'load',
        'closeViolationDeleteModal' => 'closeModal',
    ];

    /** @var ViolationDetailQuery */
    protected $detail;

    /** @var DeleteViolation */
    protected $deleteViolation;

    public function boot(ViolationDetailQuery $detail, DeleteViolation $deleteViolation)
    {
        $this->detail = $detail;
        $this->deleteViolation = $deleteViolation;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Violation::class);

        $violation = $this->detail->find($id);

        $this->deletingViolationId = $violation->id;
        $this->deletingViolationData = [
            'type'    => $violation->type_label,
            'member'  => $violation->resident_label,
            'when'    => $violation->occurred_display,
            'status'  => $violation->status_label,
        ];

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->deletingViolationId = null;
        $this->deletingViolationData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingViolationId) {
            return;
        }

        $this->authorize('delete', Violation::class);
        $this->deleteViolation->handle($this->detail->find($this->deletingViolationId), auth('web')->user());

        $this->emit('notify', 'success', 'Violation deleted successfully.');
        $this->emit('violation-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'violationDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.violations.modals.violation-delete-modal');
    }
}
