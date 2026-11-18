<?php

namespace App\Http\Livewire\Admin\UserManagement\Activity;

use App\Http\Livewire\AdminComponent;
use App\Models\AdminActivity;
use App\Queries\UserManagement\Activities\AdminActivityListQuery;
use Livewire\WithPagination;

class ActivityList extends AdminComponent
{
    use WithPagination;

    public $activityPage = 1;

    protected $listeners = [
        'activity-deleted'   => 'afterActivityDeleted',
        'activities-cleared' => 'afterActivitiesCleared',
    ];

    /** @var AdminActivityListQuery */
    protected $query;

    public function boot(AdminActivityListQuery $query)
    {
        $this->query = $query;
    }

    public function queryStringWithPagination(): array
    {
        return ['activityPage' => ['except' => 1]];
    }

    public function afterActivityDeleted(): void
    {
        $page = (int) ($this->paginators['activityPage'] ?? 1);

        if ($page > 1 && $this->query->paginate()->isEmpty()) {
            $this->previousPage('activityPage');
        }
    }

    public function afterActivitiesCleared(): void
    {
        $this->resetPage('activityPage');
    }

    public function render()
    {
        $this->authorize('viewAny', AdminActivity::class);

        return view('livewire.admin.user-management.activity.activity-list', [
            'activities' => $this->query->paginate(),
        ]);
    }
}
