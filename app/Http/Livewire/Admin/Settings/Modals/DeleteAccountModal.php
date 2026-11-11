<?php

namespace App\Http\Livewire\Admin\Settings\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Services\ActivityLogService;

/**
 * The officer's own "delete my account" confirmation.
 *
 * It lives beside the profile page rather than inside `ProfileSettings`, so the
 * page keeps only the trigger and this dialog owns what the confirmation
 * needs: the typed name it checks, the refusal when the name does not match,
 * and the delete itself. It is only mounted for the officer roles that are
 * offered it at all (see `resources/views/admin/settings/profile.blade.php`).
 */
class DeleteAccountModal extends AdminModalComponent
{
    public $deleteConfirmation = '';

    public $deleteNameError = false;

    protected $listeners = [
        'openDeleteAccountModal'  => 'load',
        'closeDeleteAccountModal' => 'closeModal',
    ];

    /** @var ActivityLogService */
    protected $activityService;

    public function boot(ActivityLogService $activityService)
    {
        $this->activityService = $activityService;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    /**
     * Ready the confirmation the browser has already opened: the typed-name
     * check always starts blank.
     */
    public function load(): void
    {
        $this->resetConfirmation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetConfirmation();

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    /**
     * Delete the authenticated officer's account and log them out.
     */
    public function delete()
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('delete', auth('web')->user());

        $user = auth('web')->user();

        // The officer has to type their own full name: a click is not a
        // decision, and this one cannot be undone.
        if ($this->deleteConfirmation !== $user->name) {
            $this->deleteNameError = true;

            return;
        }

        $this->activityService->log(
            $user,
            'deleted',
            'profile',
            "Deleted own account ({$user->name}, {$user->email})",
            ['user_id' => $user->id]
        );

        auth('web')->logout();

        $user->delete();

        session()->flash('message', 'Your account has been deleted.');

        return redirect()->route('login');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetConfirmation(): void
    {
        $this->deleteConfirmation = '';
        $this->deleteNameError = false;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.settings.modals.delete-account-modal');
    }
}
