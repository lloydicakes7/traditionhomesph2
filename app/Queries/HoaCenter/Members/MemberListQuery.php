<?php

namespace App\Queries\HoaCenter\Members;

use App\Models\HoaMember;
use App\Models\HoaMemberGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MemberListQuery
{
    const PER_PAGE = 12;

    const PREVIEW_LIMIT = 6;

    const VISIBLE_PER_GROUP = 6;

    // ===== Rows =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return HoaMember::query()
            ->with('group')
            ->search($search)
            ->ordered()
            ->paginate($perPage);
    }

    public function sections(string $search = ''): Collection
    {
        $groups = HoaMemberGroup::query()
            ->with(['members' => function ($query) use ($search) {
                $query->search($search)->ordered();
            }])
            ->whereHas('members', function ($query) use ($search) {
                $query->search($search);
            })
            ->orderByRaw(
                'CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) = ? THEN 1 ELSE 2 END',
                [strtolower(HoaMemberGroup::STAFF), strtolower(HoaMemberGroup::BOARD)]
            )
            ->orderBy('name')
            ->get();

        $groups->each(function (HoaMemberGroup $group) {
            $group->section_key = (string) $group->id;
        });

        $ungrouped = HoaMember::query()
            ->whereNull('hoa_member_group_id')
            ->search($search)
            ->ordered()
            ->get();

        if ($ungrouped->isNotEmpty()) {
            $section = new HoaMemberGroup(['name' => 'Ungrouped']);
            $section->section_key = 'ungrouped';
            $section->setRelation('members', $ungrouped);
            $groups->push($section);
        }

        return $groups;
    }

    public function groups(): array
    {
        return HoaMemberGroup::query()
            ->withCount('members')
            ->ordered()
            ->get()
            ->all();
    }

    public function preview(int $limit = self::PREVIEW_LIMIT): array
    {
        $members = HoaMember::query()->ordered()->limit($limit + 1)->get();

        return [
            'items'   => $members->take($limit),
            'hasMore' => $members->count() > $limit,
        ];
    }

    public function find(int $id): HoaMember
    {
        return HoaMember::findOrFail($id);
    }

    public function count(): int
    {
        return HoaMember::count();
    }
}
