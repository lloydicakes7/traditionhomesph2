<?php

namespace App\Http\Livewire\Admin\HoaCenter\Directory;

use App\Http\Livewire\AdminComponent;
use App\Models\Directory\DirectoryEntry;
use App\Models\Directory\DirectoryGroup;
use App\Queries\HoaCenter\Directory\DirectoryListQuery;
use Livewire\WithPagination;

class DirectoryList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search', 'groupFilter'];

    public $search = '';

    public $groupFilter = '';

    public $expanded = [];

    protected $listeners = [
        'directory-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DirectoryListQuery */
    protected $query;

    public function boot(DirectoryListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGroupFilter(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Group Expansion
    // =====================================================================

    public function showMore($groupId): void
    {
        $this->expanded[(string) $groupId] = true;
    }

    public function showLess($groupId): void
    {
        unset($this->expanded[(string) $groupId]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', DirectoryEntry::class);

        $groups = $this->query->groupedPage(
            $this->search,
            $this->groupFilter !== '' ? (int) $this->groupFilter : null
        );

        foreach ($groups->getCollection() as $group) {
            $group->visible_entries = $this->visibleEntries($group);
            $group->hidden_entries_count = $group->matching_entries_count - $group->visible_entries->count();
        }

        return view('livewire.admin.hoa-center.directory.directory-list', [
            'groups' => $groups,
            'groupFilterOptions' => $this->query->groupsWithCounts(),
        ]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function visibleEntries(DirectoryGroup $group)
    {
        return isset($this->expanded[(string) $group->id])
            ? $group->entries
            : $group->entries->take(DirectoryListQuery::VISIBLE_PER_GROUP);
    }
}
