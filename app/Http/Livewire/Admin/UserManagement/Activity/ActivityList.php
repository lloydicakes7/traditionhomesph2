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

    public $search = '';

    public $date = '';

    public $fromTime = '00:00';

    public $toTime = '23:59';

    /** @var array Selected activity IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages (the table owns the selection) */
    public $selectAll = false;

    /** @var array<string, array> Query-string persistent state */
    protected $queryString = ['activityPage' => ['except' => 1]];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'activity-deleted' => 'refreshList',
    ];

    /** @var AdminActivityListQuery */
    protected $query;

    public function boot(AdminActivityListQuery $query)
    {
        $this->query = $query;
    }

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function updated($field): void
    {
        if (in_array($field, ['search', 'date', 'fromTime', 'toTime'], true)) {
            $this->selectedIds = [];
            $this->selectAll = false;

            $this->resetPage('activityPage');
        }
    }

    /**
     * The filters as the query reads them.
     */
    private function filters(): array
    {
        return [
            'search'    => $this->search,
            'date'      => $this->date,
            'from_time' => $this->fromTime,
            'to_time'   => $this->toTime,
        ];
    }

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->filters());
    }

    /**
     * Toggle "all pages" using the filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * A log is gone: drop the selection it consumed and step back off a page
     * the delete emptied.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;

        if ($this->activityPage <= 1) {
            return;
        }

        if ($this->query->paginate($this->filters())->isEmpty()) {
            $this->previousPage('activityPage');
        }
    }

    public function render()
    {
        $this->authorize('viewAny', AdminActivity::class);

        return view('livewire.admin.user-management.activity.activity-list', [
            'activities' => $this->query->paginate($this->filters()),
        ]);
    }
}
