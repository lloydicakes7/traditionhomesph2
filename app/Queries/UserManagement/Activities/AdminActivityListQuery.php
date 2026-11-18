<?php

namespace App\Queries\UserManagement\Activities;

use App\Models\AdminActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminActivityListQuery
{
    const PER_PAGE = 15;

    public function paginate(int $perPage = self::PER_PAGE, string $pageName = 'activityPage'): LengthAwarePaginator
    {
        return AdminActivity::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $pageName);
    }
}
