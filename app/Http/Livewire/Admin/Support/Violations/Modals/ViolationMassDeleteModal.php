<?php

namespace App\Http\Livewire\Admin\Support\Violations\Modals;

use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Violation;
use App\Actions\Support\Violations\DeleteViolations;

/**
 * "Delete Selected Violations" confirmation modal.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list. It uses the shared
 * `WithMassDelete` trait for the delete flow and emits `violation-saved` on
 * completion.
 */
class ViolationMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'violationMassDeleteModal';

    /** @var array Ids received from the table's selection */
    public $selectedIds = [];

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadViolationMassDeleteModal'  => 'load',
        'closeViolationMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteViolations */
    protected $deleteViolations;

    public function boot(DeleteViolations $deleteViolations)
    {
        $this->deleteViolations = $deleteViolations;
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Violation::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'violations';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteViolations->handle($ids, auth('web')->user());
    }

    /**
     * The violations table reloads on this event — the confirmation is not part of it.
     */
    protected function massDeleteSavedEvent(): ?string
    {
        return 'violation-saved';
    }

    /**
     * Drop the selection when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.violations.modals.violation-mass-delete-modal');
    }
}
