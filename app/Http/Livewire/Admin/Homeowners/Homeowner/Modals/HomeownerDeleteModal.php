<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Actions\Homeowners\Users\DeleteUserAccounts;
use App\Http\Livewire\AdminModalComponent;
use App\Models\User;
use App\Queries\Homeowners\Users\UserDetailQuery;

class HomeownerDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'homeownerDeleteModal';

    public $deletingHomeownerId = null;

    public $deletingHomeownerEmail = null;

    protected $listeners = [
        'loadHomeownerDeleteModal'  => 'load',
        'closeHomeownerDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var UserDetailQuery */
    protected $query;

    /** @var DeleteUserAccounts */
    protected $deleteAccounts;

    public function boot(UserDetailQuery $query, DeleteUserAccounts $deleteAccounts)
    {
        $this->query = $query;
        $this->deleteAccounts = $deleteAccounts;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', User::class);

        $account = $this->query->summary((int) $id);

        $this->deletingHomeownerId = $account['id'];
        $this->deletingHomeownerEmail = $account['email'];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingHomeownerId = null;
        $this->deletingHomeownerEmail = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingHomeownerId) {
            return;
        }

        $this->authorize('delete', User::class);

        $this->deleteAccounts->handle($this->deletingHomeownerId, auth('web')->user());

        $this->emit('notify', 'success', 'Homeowner account deleted successfully.');
        $this->emit('homeowner-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.modals.homeowner-delete-modal');
    }
}
