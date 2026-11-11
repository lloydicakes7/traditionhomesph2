<?php

namespace App\Queries\HoaCenter\Announcements;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementListQuery
{
    const PER_PAGE = 9;

    const PREVIEW_LIMIT = 3;

    // ===== Rows =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Announcement::query()
            ->search($search)
            ->ordered()
            ->paginate($perPage);
    }

    public function preview(int $limit = self::PREVIEW_LIMIT): array
    {
        $posts = Announcement::query()->ordered()->limit($limit + 1)->get();

        return [
            'items'   => $posts->take($limit),
            'hasMore' => $posts->count() > $limit,
        ];
    }

    public function find(int $id): Announcement
    {
        return Announcement::findOrFail($id);
    }
}
