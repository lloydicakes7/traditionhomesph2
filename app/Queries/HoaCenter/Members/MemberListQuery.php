<?php

namespace App\Queries\HoaCenter\Members;

use App\Models\HoaMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MemberListQuery
{
    const PER_PAGE = 12;

    const PREVIEW_LIMIT = 6;

    // ===== Rows =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return HoaMember::query()
            ->search($search)
            ->ordered()
            ->paginate($perPage);
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
