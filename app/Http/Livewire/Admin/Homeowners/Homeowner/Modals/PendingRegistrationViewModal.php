<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\User;
use App\Queries\Homeowners\Homeowners\PendingRegistrationListQuery;

/**
 * The detail dialog behind one account waiting for approval: the applicant's
 * profile, the household they filed, and the valid ID they uploaded.
 */
class PendingRegistrationViewModal extends AdminModalComponent
{
    const MODAL_ID = 'pendingRegistrationViewModal';

    /** @var \App\Models\User|null The registration on screen */
    public $registration = null;

    protected $listeners = [
        'loadPendingRegistrationViewModal'  => 'load',
        'closePendingRegistrationViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PendingRegistrationListQuery */
    protected $query;

    public function boot(PendingRegistrationListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', User::class);

        $this->registration = $this->query->find((int) $id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->registration = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.modals.pending-registration-view-modal');
    }
}
