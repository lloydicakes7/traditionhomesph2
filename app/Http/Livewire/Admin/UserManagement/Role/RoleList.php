<?php

namespace App\Http\Livewire\Admin\UserManagement\Role;

use App\Http\Livewire\AdminComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Roles\RoleListQuery;
use Livewire\WithPagination;

class RoleList extends AdminComponent
{
    use WithPagination;

    protected $listeners = [
        'role-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var RoleListQuery */
    protected $query;

    public function boot(RoleListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('manageRoles', Admin::class);

        return view('livewire.admin.user-management.role.role-list', [
            'roles' => $this->query->paginate(),
        ]);
    }
}
