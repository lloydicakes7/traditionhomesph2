<?php

namespace App\Http\Livewire\Admin\UserManagement\Role\Modals;

use App\Actions\UserManagement\Roles\DeleteRole;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Roles\RoleFormQuery;


class RoleDeleteModal extends AdminModalComponent
{

    public $deletingRoleId = null;

    public $deletingRoleName = '';

    public $deletingRoleDescription = null;

    protected $listeners = [
        'loadRoleDeleteModal'  => 'load',
        'closeRoleDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var RoleFormQuery */
    protected $formQuery;

    /** @var DeleteRole */
    protected $deleteRole;

    public function boot(RoleFormQuery $formQuery, DeleteRole $deleteRole)
    {
        $this->formQuery = $formQuery;
        $this->deleteRole = $deleteRole;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('manageRoles', Admin::class);

        $role = $this->formQuery->find($id);

        if ($blocker = $role->deletionBlocker()) {
            $this->emit('notify', 'error', $blocker);
            $this->dispatchBrowserEvent('modal-close', ['id' => 'roleDeleteModal']);

            return;
        }

        $this->deletingRoleId = $role->id;
        $this->deletingRoleName = $role->name;
        $this->deletingRoleDescription = $role->description;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingRoleId = null;
        $this->deletingRoleName = '';
        $this->deletingRoleDescription = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingRoleId) {
            return;
        }

        $this->authorize('manageRoles', Admin::class);

        try {
            $this->deleteRole->handle($this->formQuery->find($this->deletingRoleId), auth('web')->user());
            $this->emit('notify', 'success', 'Role deleted.');
        } catch (\RuntimeException $e) {
            $this->emit('notify', 'error', $e->getMessage());
        }

        $this->emit('role-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'roleDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('manageRoles', Admin::class);

        return view('livewire.admin.user-management.role.modals.role-delete-modal');
    }
}
