<?php

namespace App\Queries\UserManagement\Users;

use App\Models\Admin;
use App\Models\AdminActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserDetailQuery
{
    // ===== Queries =====

    public function find(int $id): Admin
    {
        return Admin::with('role')->findOrFail($id);
    }

    public function activities(int $adminId, int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator
    {
        return AdminActivity::query()
            ->where('admin_id', $adminId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], $pageName);
    }
}
