<?php

namespace App\Queries\UserManagement\Users;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserListQuery
{
    const PER_PAGE = 15;

    const SORTABLE = ['id', 'name', 'email', 'is_active', 'created_at'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return Admin::query()
            ->with('role:id,name')
            ->search((string) ($criteria['search'] ?? ''))
            ->orderBy($this->sortColumn($criteria['sort'] ?? null), $this->direction($criteria['direction'] ?? null))
            ->paginate($criteria['perPage'] ?? self::PER_PAGE);
    }

    // ===== Helpers =====

    private function sortColumn(?string $field): string
    {
        return in_array($field, self::SORTABLE, true) ? $field : 'id';
    }

    private function direction(?string $direction): string
    {
        return $direction === 'asc' ? 'asc' : 'desc';
    }
}
