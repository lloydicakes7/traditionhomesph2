<?php

namespace App\Queries\HoaCenter\Directory;

use App\Models\Directory\DirectoryEntry;
use App\Models\Directory\DirectoryGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DirectoryListQuery
{
    const GROUPS_PER_PAGE = 4;

    const VISIBLE_PER_GROUP = 6;

    // ===== Rows =====

    public function groupedPage(
        string $search = '',
        $groupFilter = null,
        int $perPage = self::GROUPS_PER_PAGE
    ): LengthAwarePaginator {
        $groups = DirectoryGroup::query()
            ->when($groupFilter, function ($query) use ($groupFilter) {
                $query->whereKey((int) $groupFilter);
            })
            ->whereHas('entries', function ($query) use ($search) {
                $query->search($search);
            })
            ->orderBy('name')
            ->paginate($perPage);

        $groups->getCollection()->load(['entries' => function ($query) use ($search) {
            $query->search($search)->orderBy('id')->with('phones');
        }]);

        foreach ($groups->getCollection() as $group) {
            $group->matching_entries_count = $group->entries->count();
        }

        return $groups;
    }

    public function groupsWithCounts(): Collection
    {
        return DirectoryGroup::withCount('entries')->ordered()->get();
    }

    public function stats(): array
    {
        return [
            'total'  => DirectoryEntry::count(),
            'groups' => DirectoryGroup::count(),
        ];
    }

    public function find(int $id): DirectoryEntry
    {
        return DirectoryEntry::with(['group', 'phones'])->findOrFail($id);
    }
}
