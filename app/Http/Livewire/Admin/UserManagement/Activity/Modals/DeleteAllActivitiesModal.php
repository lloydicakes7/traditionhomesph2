<?php

namespace App\Http\Livewire\Admin\UserManagement\Activity\Modals;

use App\Actions\UserManagement\Activities\DeleteAllAdminActivities;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdminActivity;

class DeleteAllActivitiesModal extends AdminModalComponent
{
    const MODAL_ID = 'deleteAllActivitiesModal';

    public $activityCount = 0;

    protected $listeners = [
        'openDeleteAllActivitiesModal'  => 'open',
        'closeDeleteAllActivitiesModal' => 'closeModal',
    ];

    /** @var DeleteAllAdminActivities */
    protected $deleteActivities;

    public function boot(DeleteAllAdminActivities $deleteActivities)
    {
        $this->deleteActivities = $deleteActivities;
    }

    public function open(): void
    {
        $this->authorize('delete', AdminActivity::class);

        $this->activityCount = AdminActivity::query()->count();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->activityCount = 0;

        parent::resetModal();
    }

    public function deleteAll(): void
    {
        if (! $this->loaded || $this->activityCount === 0) {
            return;
        }

        $this->authorize('delete', AdminActivity::class);

        $this->deleteActivities->handle();
        $this->activityCount = 0;

        $this->emit('notify', 'success', 'All activity logs deleted.');
        $this->emit('activities-cleared');
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    public function render()
    {
        $this->authorize('delete', AdminActivity::class);

        return view('livewire.admin.user-management.activity.modals.delete-all-activities-modal');
    }
}
