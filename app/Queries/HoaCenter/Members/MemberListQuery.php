<?php

namespace App\Queries\HoaCenter\Members;

use App\Models\HoaMember;
use App\Models\HoaMemberGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MemberListQuery
{
    const PER_PAGE = 30;

    const PREVIEW_LIMIT = 6;

    const EXECUTIVE_POSITIONS = [
        'President'      => 0,
        'Vice President' => 1,
        'Vice-President' => 1,
        'Secretary'      => 2,
        'Treasurer'      => 3,
        'Auditor'        => 4,
    ];

    const BOARD_SEQUENCE = [
        'Rolando M. Mirano',
        'Reginalda A. Payot',
        'Rowena L. Echavez',
        'Nelzon L. Zabate',
        'Divina T. Larong',
        'Lilia R. Bale',
        'Paulino P. Coronel',
        'Medin R. Maraon',
        'Bernadette P. Alba',
        'Rizza U. Sayre',
    ];

    const COMMITTEE_SEQUENCE = [
        'John Paul R. Garrido',
        'Baby Jane S. Sajol',
        'Primrose L. Doroteo',
        'Miguelito E. Loreño',
        'Merlina H. Acosta',
    ];

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
            ->whereHas('members', function ($query) use ($search) {
                $query->search($search);
            })
            ->ordered()
            ->get();

        $groups->each(function (HoaMemberGroup $group) use ($search) {
            $group->section_key = (string) $group->id;

            $this->attachMembers($group, $search);
        });

        $ungrouped = new HoaMemberGroup(['name' => 'Ungrouped']);
        $ungrouped->section_key = 'ungrouped';

        $this->attachMembers($ungrouped, $search);

        if ($ungrouped->paginator->total() > 0) {
            $groups->push($ungrouped);
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

    public function pageNames(): array
    {
        $names = HoaMemberGroup::query()
            ->pluck('id')
            ->map(fn ($id) => $this->pageName((string) $id))
            ->all();

        $names[] = $this->pageName('ungrouped');

        return $names;
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

    // ===== Helpers =====

    private function attachMembers(HoaMemberGroup $group, string $search): void
    {
        $group->paginator = $this->memberQuery($group, $search)
            ->paginate(self::PER_PAGE, ['*'], $this->pageName($group->section_key));

        $group->setRelation('members', $group->paginator->getCollection());
    }

    private function memberQuery(HoaMemberGroup $group, string $search): Builder
    {
        $query = HoaMember::query()->search($search);

        if ($group->isExecutive()) {
            $query->inPositionOrder(self::EXECUTIVE_POSITIONS);
        } elseif ($group->isBoard()) {
            $query->inNameOrder(self::BOARD_SEQUENCE);
        } elseif ($group->isCommittee()) {
            $query->inNameOrder(self::COMMITTEE_SEQUENCE);
        } else {
            $query->ordered();
        }

        return $group->section_key === 'ungrouped'
            ? $query->whereNull('hoa_member_group_id')
            : $query->where('hoa_member_group_id', $group->id);
    }

    private function pageName(string $sectionKey): string
    {
        return 'members_page_' . $sectionKey;
    }
}
