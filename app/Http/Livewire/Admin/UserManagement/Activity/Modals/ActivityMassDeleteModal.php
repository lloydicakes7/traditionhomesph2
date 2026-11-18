<?php

namespace App\Http\Livewire\Admin\UserManagement\Activity\Modals;

use App\Actions\UserManagement\Activities\DeleteAdminActivities;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Models\AdminActivity;

/**
 * Delete Selected Activity Logs, the confirmation the trail's mass-delete bar
 * opens.
 *
 * The bar is a plain Bootstrap trigger carrying the selected ids as the
 * loader's single `data-id` (public/js/modal-loader.js), so the dialog opens at
 * the click and loads the selection without a round trip to the list.
 */
class ActivityMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'activityMassDeleteModal';

    public $selectedIds = [];

    protected $listeners = [
        'loadActivityMassDeleteModal'  => 'load',
        'closeActivityMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteAdminActivities */
    protected $deleteActivities;

    public function boot(DeleteAdminActivities $deleteActivities)
    {
        $this->deleteActivities = $deleteActivities;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    protected function massDeleteAuthorizable(): string
    {
        return AdminActivity::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'activity logs';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteActivities->handle($ids);
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'activity-deleted';
    }

    public function render()
    {
        $this->authorize('delete', AdminActivity::class);

        return view('livewire.admin.user-management.activity.modals.activity-mass-delete-modal');
    }
}
