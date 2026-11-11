<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Actions\UserManagement\Users\DeleteAdmin;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Users\UserDetailQuery;

class UserDeleteModal extends AdminModalComponent
{
    public $deletingAdminId = null;

    public $deletingAdminName = '';

    public $deletingAdminEmail = '';

    protected $listeners = [
        'loadUserDeleteModal'  => 'load',
        'closeUserDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var UserDetailQuery */
    protected $detail;

    /** @var DeleteAdmin */
    protected $delete;

    public function boot(UserDetailQuery $detail, DeleteAdmin $delete)
    {
        $this->detail = $detail;
        $this->delete = $delete;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $admin = $this->detail->find($id);
        $this->authorize('delete', $admin);

        $this->deletingAdminId = $admin->id;
        $this->deletingAdminName = $admin->name;
        $this->deletingAdminEmail = $admin->email;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingAdminId = null;
        $this->deletingAdminName = '';
        $this->deletingAdminEmail = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAdminId) {
            return;
        }

        $admin = $this->detail->find($this->deletingAdminId);

        $this->authorize('delete', $admin);

        $this->delete->handle($admin, auth('web')->user());

        $this->emit('notify', 'success', 'Account deleted.');
        $this->emit('user-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'userDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Admin::class);

        return view('livewire.admin.user-management.user.modals.user-delete-modal');
    }
}
