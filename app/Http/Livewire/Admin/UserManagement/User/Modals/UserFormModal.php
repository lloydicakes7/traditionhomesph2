<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Actions\UserManagement\Users\CreateAdmin;
use App\Actions\UserManagement\Users\UpdateAdmin;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Users\UserDetailQuery;
use App\Queries\UserManagement\Users\UserFormQuery;

/**
 * Create / Edit Admin — an officer's sign-in account and the role it holds.
 *
 * Opens instantly through Bootstrap (`#userFormModal`, triggered from the list
 * with `data-toggle`); the record is fetched lazily afterwards, when
 * `public/js/modal-loader.js` fires `loadUserFormModal` (edit) or
 * `openUserFormModal` (create). Saving emits `user-saved` so the list refreshes.
 *
 * No password is asked for here: a new account is created with one nobody
 * knows and the officer is mailed a link to choose their own. A lost password
 * is replaced from the list's Reset Password action, never typed by an officer.
 */
class UserFormModal extends AdminModalComponent
{
    /** @var bool Whether an existing account is being edited */
    public $isEditing = false;

    /** @var int|null The account being edited; null while creating one */
    public $editingAdminId = null;

    /** @var array<string, mixed> The account's own columns as form state */
    public $form = [
        'name'      => '',
        'email'     => '',
        'role_id'   => '',
        'is_active' => true,
    ];

    /** @var array<string, string> Modal open / load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openUserFormModal'  => 'openCreate',
        'loadUserFormModal'  => 'openEdit',
        'closeUserFormModal' => 'closeModal',
    ];

    /** @var UserDetailQuery */
    protected $detail;

    /** @var UserFormQuery */
    protected $formQuery;

    /** @var CreateAdmin */
    protected $create;

    /** @var UpdateAdmin */
    protected $update;

    public function boot(
        UserDetailQuery $detail,
        UserFormQuery $formQuery,
        CreateAdmin $create,
        UpdateAdmin $update
    ) {
        $this->detail = $detail;
        $this->formQuery = $formQuery;
        $this->create = $create;
        $this->update = $update;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        $email = 'unique:admins,email';

        if ($this->isEditing && $this->editingAdminId) {
            $email .= ',' . $this->editingAdminId;
        }

        return [
            'form.name'    => ['required', 'string', 'max:255'],
            'form.email'   => ['required', 'string', 'email', 'max:255', $email],
            'form.role_id' => ['required', 'exists:roles,id'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name'    => 'full name',
            'form.email'   => 'email address',
            'form.role_id' => 'role',
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Admin::class);

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $admin = $this->detail->find($id);
        $this->authorize('update', $admin);

        $this->resetForm();
        $this->resetValidation();

        $this->isEditing = true;
        $this->editingAdminId = $admin->id;
        $this->form = [
            'name'      => $admin->name,
            'email'     => $admin->email,
            'role_id'   => (int) $admin->role_id,
            'is_active' => (bool) $admin->is_active,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetForm();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $admin = $this->editingAdminId ? $this->detail->find($this->editingAdminId) : null;

        $this->authorize($admin ? 'update' : 'create', $admin ?: Admin::class);
        $this->validate();

        $by = auth('web')->user();

        if ($admin) {
            $this->update->handle($admin, $this->form, $by);
            $this->emit('notify', 'success', 'Account updated.');
        } else {
            $this->create->handle($this->form, $by);
            $this->emit('notify', 'success', 'Account created.');
        }

        $this->emit('user-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'userFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingAdminId = null;
        $this->form = [
            'name'      => '',
            'email'     => '',
            'role_id'   => '',
            'is_active' => true,
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Admin::class);

        return view('livewire.admin.user-management.user.modals.user-form-modal', [
            'roles' => $this->loaded ? $this->formQuery->roles() : collect(),
        ]);
    }
}
