<?php

namespace App\Http\Livewire\Admin\UserManagement\Role\Modals;

use App\Actions\UserManagement\Roles\CreateRole;
use App\Actions\UserManagement\Roles\UpdateRole;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Roles\RoleFormQuery;
use App\Utility\PermissionHelper;

class RoleFormModal extends AdminModalComponent
{
    public $isEditing = false;

    public $permissionsLocked = false;

    public $editingRoleId = null;

    public $form = [
        'name'        => '',
        'description' => '',
    ];

    public $selectedPermissions = [];

    public $permissionSearch = '';

    public $activeModule = null;

    protected $listeners = [
        'openRoleFormModal'  => 'openCreate',
        'loadRoleFormModal'  => 'openEdit',
        'closeRoleFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var RoleFormQuery */
    protected $formQuery;

    /** @var CreateRole */
    protected $create;

    /** @var UpdateRole */
    protected $update;

    public function boot(RoleFormQuery $formQuery, CreateRole $create, UpdateRole $update)
    {
        $this->formQuery = $formQuery;
        $this->create = $create;
        $this->update = $update;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        $unique = 'unique:roles,name';

        if ($this->isEditing && $this->editingRoleId) {
            $unique .= ',' . $this->editingRoleId;
        }

        return [
            'form.name'        => ['required', 'string', 'max:255', $unique],
            'form.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name'        => 'role name',
            'form.description' => 'description',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('manageRoles', Admin::class);

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('manageRoles', Admin::class);

        $role = $this->formQuery->find($id);

        $this->resetForm();
        $this->resetValidation();

        $this->isEditing = true;
        $this->editingRoleId = $role->id;
        $this->permissionsLocked = $role->hasFullAccess();
        $this->form = [
            'name'        => $role->name,
            'description' => (string) $role->description,
        ];
        $this->selectedPermissions = $role->permissionKeys();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetForm();

        parent::resetModal();
    }

    // =====================================================================
    // Permission catalogue
    // =====================================================================

    public function selectModule(string $module): void
    {
        $this->activeModule = $module;
    }

    public function toggleModule(): void
    {
        if ($this->permissionsLocked || ! $this->activeModule) {
            return;
        }

        $keys = PermissionHelper::keysForModule($this->activeModule);

        $this->selectedPermissions = $this->moduleFullySelected($this->activeModule)
            ? array_values(array_diff($this->selectedPermissions, $keys))
            : array_values(array_unique(array_merge($this->selectedPermissions, $keys)));
    }

    public function grantAll(): void
    {
        if ($this->permissionsLocked) {
            return;
        }

        $this->selectedPermissions = PermissionHelper::keys();
    }

    public function revokeAll(): void
    {
        if ($this->permissionsLocked) {
            return;
        }

        $this->selectedPermissions = [];
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('manageRoles', Admin::class);
        $this->validate();

        $by = auth('web')->user();

        if ($this->isEditing) {
            $this->update->handle(
                $this->formQuery->find($this->editingRoleId),
                $this->form,
                $this->selectedPermissions,
                $by
            );
            $this->emit('notify', 'success', 'Role updated.');
        } else {
            $this->create->handle($this->form, $this->selectedPermissions, $by);
            $this->emit('notify', 'success', 'Role created.');
        }

        $this->emit('role-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'roleFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetForm(): void
    {
        $this->isEditing = false;
        $this->permissionsLocked = false;
        $this->editingRoleId = null;
        $this->form = [
            'name'        => '',
            'description' => '',
        ];
        $this->selectedPermissions = [];
        $this->permissionSearch = '';
        $this->activeModule = null;
    }

    private function moduleFullySelected(string $module): bool
    {
        $keys = PermissionHelper::keysForModule($module);

        return $keys !== [] && array_diff($keys, $this->selectedPermissions) === [];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('manageRoles', Admin::class);

        $counts = $this->moduleCounts();
        $active = $this->activeModule;
        $full = $active ? $this->moduleFullySelected($active) : false;

        return view('livewire.admin.user-management.role.modals.role-form-modal', [
            'permissionsLocked' => $this->permissionsLocked,
            'catalogue'         => $this->catalogue($counts),
            'activeModule'      => $active,
            'activeGroup'       => $active ? PermissionHelper::groupOf($active) : null,
            'activePermissions' => $this->activePermissions(),
            'activeGranted'     => $active ? $counts[$active] : 0,
            'activeToggle'      => [
                'label' => $full ? 'Revoke all' : 'Grant all',
                'theme' => $full ? 'danger' : 'primary',
                'icon'  => $full ? 'fas fa-fw fa-times-circle' : 'fas fa-fw fa-check-circle',
            ],
            'selectedCount'     => count($this->selectedPermissions),
            'totalCount'        => count(PermissionHelper::keys()),
        ]);
    }

    // =====================================================================
    // Views
    // =====================================================================

    private function catalogue(array $counts): array
    {
        $groups = [];

        foreach (PermissionHelper::search($this->permissionSearch) as $group => $modules) {
            foreach ($modules as $module => $types) {
                $granted = $counts[$module] ?? 0;
                $total = count($types);

                $groups[$group][] = [
                    'name'    => $module,
                    'granted' => $granted,
                    'total'   => $total,
                    'active'  => $this->activeModule === $module,
                    'theme'   => $granted === 0 ? 'secondary' : ($granted === $total ? 'success' : 'info'),
                ];
            }
        }

        return $groups;
    }

    private function moduleCounts(): array
    {
        $counts = [];

        foreach (PermissionHelper::modules() as $module => $types) {
            $counts[$module] = count(array_intersect(PermissionHelper::keysForModule($module), $this->selectedPermissions));
        }

        return $counts;
    }

    private function activePermissions(): array
    {
        if (! $this->activeModule || ! PermissionHelper::hasModule($this->activeModule)) {
            return [];
        }

        $rows = [];

        foreach (PermissionHelper::typesFor($this->activeModule) as $type) {
            $key = PermissionHelper::key($this->activeModule, $type);

            $rows[] = [
                'key'      => $key,
                'label'    => $type,
                'note'     => PermissionHelper::note($type),
                'selected' => in_array($key, $this->selectedPermissions, true),
            ];
        }

        return $rows;
    }
}
