<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Actions\Homeowners\Users\DeleteUserAccounts;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\User;

/**
 * Delete Selected Homeowners — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class HomeownerMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'homeownerMassDeleteModal';

    public $selectedIds = [];

    protected $listeners = [
        'loadHomeownerMassDeleteModal'  => 'load',
        'closeHomeownerMassDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DeleteUserAccounts */
    protected $deleteAccounts;

    public function boot(DeleteUserAccounts $deleteAccounts)
    {
        $this->deleteAccounts = $deleteAccounts;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return User::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'homeowners';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteAccounts->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'homeowner-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.modals.homeowner-mass-delete-modal');
    }
}
