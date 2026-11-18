<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Actions\UserManagement\Activities\DeleteAdminActivities;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdminActivity;
use App\Queries\UserManagement\Users\UserDetailQuery;

/**
 * Delete All Activity Logs, the confirmation the admin's activities tab opens
 * from its top-right button.
 *
 * The button is a plain Bootstrap trigger carrying the admin's id as the
 * loader's single `data-id` (public/js/modal-loader.js), so the dialog opens at
 * the click and loads the count without a round trip to the view modal.
 */
class UserActivityDeleteAllModal extends AdminModalComponent
{
    public $deletingAdminId = null;

    public $deletingAdminName = '';

    public $activityCount = 0;

    protected $listeners = [
        'loadActivityDeleteAllModal'  => 'load',
        'closeActivityDeleteAllModal' => 'closeModal',
    ];

    /** @var UserDetailQuery */
    protected $detail;

    /** @var DeleteAdminActivities */
    protected $deleteActivities;

    public function boot(UserDetailQuery $detail, DeleteAdminActivities $deleteActivities)
    {
        $this->detail = $detail;
        $this->deleteActivities = $deleteActivities;
    }

    protected function resetModal(): void
    {
        $this->deletingAdminId = null;
        $this->deletingAdminName = '';
        $this->activityCount = 0;

        parent::resetModal();
    }

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', AdminActivity::class);

        $admin = $this->detail->find($id);

        $this->deletingAdminId = $admin->id;
        $this->deletingAdminName = $admin->name;
        $this->activityCount = $this->detail->activityCount($admin->id);

        $this->markLoaded();
    }

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAdminId) {
            return;
        }

        $this->authorize('delete', AdminActivity::class);

        $deleted = $this->deleteActivities->forAdmin($this->deletingAdminId);

        $this->emit('notify', 'success', "{$deleted} activity logs deleted.");
        $this->emit('activity-deleted');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'activityDeleteAllModal']);
    }

    public function render()
    {
        $this->authorize('delete', AdminActivity::class);

        return view('livewire.admin.user-management.user.modals.user-activity-delete-all-modal');
    }
}
