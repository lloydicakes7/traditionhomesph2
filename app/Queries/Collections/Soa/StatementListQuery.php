<?php

namespace App\Queries\Collections\Soa;

use App\Models\SoaEmail;
use App\Utility\CacheHelper;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StatementListQuery
{
    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $filters = $criteria['filters'] ?? [];

        return SoaEmail::query()
            ->with(['user.household.property', 'user.resident'])
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->forPeriod($filters['period'] ?? null)
            ->ordered()
            ->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        $filters = $criteria['filters'] ?? [];

        return SoaEmail::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->forPeriod($filters['period'] ?? null)
            ->pluck('id')
            ->all();
    }

    public function find(int $id): SoaEmail
    {
        return SoaEmail::with(['user.household.property.address', 'user.resident'])
            ->findOrFail($id);
    }

    // ===== Options =====

    public function periods(int $back = 12): array
    {
        return CacheHelper::remember('collections.statement.options', ['periods', $back], 3600, function () use ($back) {
            return PeriodHelper::recentMonths($back);
        });
    }
}
