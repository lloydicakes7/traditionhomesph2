<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Actions\UserManagement\Users\ResetAdminPassword;
use App\Http\Livewire\AdminModalComponent;
use App\Queries\UserManagement\Users\UserDetailQuery;

/**
 * "Reset Password" for an officer's account — the password on file becomes
 * unusable and a fresh link is mailed. The resetting officer never sees or
 * chooses a password.
 */
class AdminResetPasswordModal extends AdminModalComponent
{
    const MODAL_ID = 'adminResetPasswordModal';

    /** @var int|null The account being reset */
    public $resettingAdminId = null;

    /** @var string|null The account's email, shown in the confirmation */
    public $resettingAdminEmail = null;

    /** @var array<string, string> Modal open / load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAdminResetPasswordModal'  => 'load',
        'closeAdminResetPasswordModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var UserDetailQuery */
    protected $detail;

    /** @var ResetAdminPassword */
    protected $resetPassword;

    public function boot(UserDetailQuery $detail, ResetAdminPassword $resetPassword)
    {
        $this->detail = $detail;
        $this->resetPassword = $resetPassword;
    }

    // ===== Load =====

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $admin = $this->detail->find($id);
        $this->authorize('resetPassword', $admin);

        $this->resettingAdminId = $admin->id;
        $this->resettingAdminEmail = $admin->email;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resettingAdminId = null;
        $this->resettingAdminEmail = null;

        parent::resetModal();
    }

    // ===== Reset =====

    public function resetPassword(): void
    {
        if (! $this->loaded || ! $this->resettingAdminId) {
            return;
        }

        $admin = $this->detail->find($this->resettingAdminId);
        $this->authorize('resetPassword', $admin);

        $this->resetPassword->handle($admin->id, auth('web')->user());

        $this->emit('notify', 'success', __('users.reset.sent'));
        $this->emit('user-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.user-management.user.modals.admin-reset-password-modal');
    }
}
