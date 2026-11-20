<?php

namespace App\Http\Livewire\Admin\Homeowners\Verification\Modals;

use App\Actions\Homeowners\Profiles\RejectProfile;
use App\Actions\Homeowners\Profiles\VerifyProfile;
use App\Exceptions\ProfileApprovalBlockedException;
use App\Http\Livewire\AdminModalComponent;
use App\Models\User;
use App\Queries\Homeowners\Profiles\ProfileVerificationDetailQuery;

/**
 * "Profile for Verification" modal — the person, the unit, the household and the
 * submitted ID, with the office's two answers.
 *
 * Opened from the queue's Review button; modal-loader emits
 * `loadProfileVerificationViewModal` with the account's id. A decision is
 * re-authorized here, because a Livewire update never passes the route's own
 * middleware.
 */
class ProfileVerificationViewModal extends AdminModalComponent
{
    const MODAL_ID = 'profileVerificationViewModal';

    /** @var \App\Models\User|null */
    public $verificationAccount = null;

    /** @var string The correction the resident is asked for */
    public $note = '';

    protected $listeners = [
        'loadProfileVerificationViewModal'  => 'load',
        'closeProfileVerificationViewModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var ProfileVerificationDetailQuery */
    protected $query;

    public function boot(ProfileVerificationDetailQuery $query): void
    {
        $this->query = $query;
    }

    // ===== Lifecycle =====

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewProfileVerifications', User::class);

        $this->verificationAccount = $this->query->find((int) $id);
        $this->note = '';

        $this->markLoaded();
    }

    // ===== Actions =====

    public function approve(): void
    {
        $account = $this->verificationAccount;

        if (! $account) {
            return;
        }

        $this->authorize('verifyProfile', $account);

        try {
            app(VerifyProfile::class)->handle($account, auth('web')->user());
        } catch (ProfileApprovalBlockedException $exception) {
            $this->emit('notify', 'warning', $exception->getMessage());

            return;
        }

        $this->finish(__('users.verification.approved_message'));
    }

    public function reject(): void
    {
        $account = $this->verificationAccount;

        if (! $account) {
            return;
        }

        $this->authorize('verifyProfile', $account);

        $this->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        app(RejectProfile::class)->handle($account, (string) $this->note, auth('web')->user());

        $this->finish(__('users.verification.rejected_message'));
    }

    // ===== Helpers =====

    private function finish(string $message): void
    {
        $this->emit('profile-verification-decided');
        $this->emit('notify', 'success', $message);

        $this->dispatchBrowserEvent('modal-close', self::MODAL_ID);

        $this->resetModal();
    }

    protected function resetModal(): void
    {
        $this->verificationAccount = null;
        $this->note = '';

        parent::resetModal();
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.homeowners.verification.modals.profile-verification-view-modal');
    }
}
