<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Actions\UserManagement\Activities\DeleteAdminActivities;
use App\Http\Livewire\AdminModalComponent;
use App\Models\AdminActivity;

class UserActivityDeleteModal extends AdminModalComponent
{
    public $deletingActivityId = null;

    public $deletingActivityDescription = '';

    public $deletingActivityLabel = '';

    public $deletingActivityTheme = 'secondary';

    public $deletingActivityDate = '';

    protected $listeners = [
        'loadActivityDeleteModal'  => 'load',
        'closeActivityDeleteModal' => 'closeModal',
    ];

    /** @var DeleteAdminActivities */
    protected $deleteActivities;

    public function boot(DeleteAdminActivities $deleteActivities)
    {
        $this->deleteActivities = $deleteActivities;
    }

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', AdminActivity::class);

        $activity = AdminActivity::findOrFail($id);

        $this->deletingActivityId = $activity->id;
        $this->deletingActivityDescription = (string) $activity->description;
        $this->deletingActivityLabel = $activity->action_label;
        $this->deletingActivityTheme = $activity->action_theme;
        $this->deletingActivityDate = $activity->created_at->format('M d, Y h:i A');

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingActivityId = null;
        $this->deletingActivityDescription = '';
        $this->deletingActivityLabel = '';
        $this->deletingActivityTheme = 'secondary';
        $this->deletingActivityDate = '';

        parent::resetModal();
    }

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingActivityId) {
            return;
        }

        $this->authorize('delete', AdminActivity::class);

        $this->deleteActivities->handle([$this->deletingActivityId]);

        $this->emit('notify', 'success', 'Activity log deleted.');
        $this->emit('activity-deleted');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'activityDeleteModal']);
    }

    public function render()
    {
        $this->authorize('delete', AdminActivity::class);

        return view('livewire.admin.user-management.user.modals.user-activity-delete-modal');
    }
}
