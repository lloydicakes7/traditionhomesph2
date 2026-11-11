<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Actions\Homeowners\Users\ResetHomeownerPassword;
use App\Http\Livewire\AdminModalComponent;
use App\Models\User;
use App\Queries\Homeowners\Users\UserDetailQuery;

/**
 * "Reset Password" — invalidate the account's password and mail a fresh
 * set-password link. The officer never sees or chooses a password.
 */
class HomeownerResetPasswordModal extends AdminModalComponent
{
    const MODAL_ID = 'homeownerResetPasswordModal';

    /** @var int|null The account being reset */
    public $resettingUserId = null;

    /** @var string|null The account's email, shown in the confirmation */
    public $resettingHomeownerEmail = null;

    protected $listeners = [
        'loadHomeownerResetPasswordModal'  => 'load',
        'closeHomeownerResetPasswordModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var UserDetailQuery */
    protected $query;

    /** @var ResetHomeownerPassword */
    protected $resetPassword;

    public function boot(UserDetailQuery $query, ResetHomeownerPassword $resetPassword)
    {
        $this->query = $query;
        $this->resetPassword = $resetPassword;
    }

    // ===== Load =====

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('resetPassword', User::class);

        $account = $this->query->summary((int) $id);

        $this->resettingUserId = $account['id'];
        $this->resettingHomeownerEmail = $account['email'];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resettingUserId = null;
        $this->resettingHomeownerEmail = null;

        parent::resetModal();
    }

    // ===== Reset =====

    public function resetPassword(): void
    {
        if (! $this->loaded || ! $this->resettingUserId) {
            return;
        }

        $this->authorize('resetPassword', User::class);

        $this->resetPassword->handle($this->resettingUserId, auth('web')->user());

        $this->emit('notify', 'success', 'A set-password link has been emailed to the homeowner.');
        $this->emit('homeowner-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.modals.homeowner-reset-password-modal');
    }
}
