<?php

namespace App\Http\Livewire\Admin\UserManagement\User\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Admin;
use App\Queries\UserManagement\Users\UserDetailQuery;
use Livewire\WithPagination;

class UserViewModal extends AdminModalComponent
{
    use WithPagination;

    public $viewingAdminId = null;

    public $activeViewTab = 'overview';

    public $activitiesPage = 1;

    const TABS = ['overview', 'activities'];

    protected $listeners = [
        'loadUserViewModal'  => 'load',
        'closeUserViewModal' => 'closeModal',
        'user-saved'         => '$refresh',
        'activity-deleted'   => 'afterActivityDeleted',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var UserDetailQuery */
    protected $detail;

    public function boot(UserDetailQuery $detail)
    {
        $this->detail = $detail;
    }

    // =====================================================================
    // Query string
    // =====================================================================

    public function queryStringWithPagination(): array
    {
        return ['activitiesPage' => ['except' => 1]];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $admin = $this->detail->find($id);
        $this->authorize('view', Admin::class);

        $this->viewingAdminId = $admin->id;
        $this->activeViewTab = 'overview';
        $this->activitiesPage = 1;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewingAdminId = null;
        $this->activeViewTab = 'overview';
        $this->activitiesPage = 1;

        parent::resetModal();
    }

    // =====================================================================
    // Tabs
    // =====================================================================

    public function switchViewTab(string $tab): void
    {
        if (in_array($tab, self::TABS, true)) {
            $this->activeViewTab = $tab;
        }
    }

    // =====================================================================
    // Activities
    // =====================================================================

    /**
     * The activity-log dialog deleted one record.
     *
     * The delete itself is the dialog's (`UserActivityDeleteModal`, reached by
     * the row's own `data-toggle` trigger), so all that is left here is the
     * list's bookkeeping: an emptied last page is not left standing over
     * nothing.
     */
    public function afterActivityDeleted(): void
    {
        if (! $this->viewingAdminId || $this->activitiesPage <= 1) {
            return;
        }

        if ($this->detail->activities($this->viewingAdminId, 10, 'activitiesPage')->isEmpty()) {
            $this->previousPage('activitiesPage');
        }
    }

    private function showingActivities(): bool
    {
        return $this->activeViewTab === 'activities';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $viewingAdmin = $this->viewingAdminId ? $this->detail->find($this->viewingAdminId) : null;

        if ($viewingAdmin) {
            $this->authorize('view', $viewingAdmin);
        }

        return view('livewire.admin.user-management.user.modals.user-view-modal', [
            'viewingAdmin'      => $viewingAdmin,
            'viewingActivities' => $this->showingActivities() && $viewingAdmin
                ? $this->detail->activities($viewingAdmin->id, 10, 'activitiesPage')
                : null,
        ]);
    }
}
