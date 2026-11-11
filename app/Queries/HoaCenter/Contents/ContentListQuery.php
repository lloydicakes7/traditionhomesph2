<?php

namespace App\Queries\HoaCenter\Contents;

use App\Models\Content;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContentListQuery
{
    const PER_PAGE = 15;

    // ===== Queries =====

    public function paginate(string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Content::query()
            ->search($search)
            ->ordered()
            ->paginate($perPage);
    }

    public function find(int $id): Content
    {
        return Content::findOrFail($id);
    }
}
