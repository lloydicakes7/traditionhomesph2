<?php

namespace App\Http\Livewire\Admin\UserManagement\User;

use App\Actions\UserManagement\Users\ToggleAdminActive;
use App\Http\Livewire\AdminComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Users\UserDetailQuery;
use App\Queries\UserManagement\Users\UserListQuery;
use Livewire\WithPagination;

class UserList extends AdminComponent
{
    use WithPagination;

    public $search = '';

    public $sortField = 'id';

    public $sortDirection = 'desc';

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    protected $listeners = [
        'user-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var UserListQuery */
    protected $list;

    /** @var UserDetailQuery */
    protected $detail;

    /** @var ToggleAdminActive */
    protected $toggleActive;

    public function boot(UserListQuery $list, UserDetailQuery $detail, ToggleAdminActive $toggleActive)
    {
        $this->list = $list;
        $this->detail = $detail;
        $this->toggleActive = $toggleActive;
    }

    // =====================================================================
    // Search and Sort
    // =====================================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    // =====================================================================
    // Status
    // =====================================================================

    public function toggleActive($id): void
    {
        $admin = $this->detail->find($id);
        $this->authorize('toggleActive', $admin);

        $isActive = $this->toggleActive->handle($admin, auth('web')->user());

        $this->emit('notify', 'success', $admin->name . ($isActive ? ' enabled.' : ' disabled.'));
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Admin::class);

        return view('livewire.admin.user-management.user.user-list', [
            'admins' => $this->list->paginate([
                'search'    => $this->search,
                'sort'      => $this->sortField,
                'direction' => $this->sortDirection,
            ]),
        ]);
    }
}
