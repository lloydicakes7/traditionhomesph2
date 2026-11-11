<?php

namespace App\Queries\Collections\MonthlyDues;

use App\Models\Bill\Bill;
use App\Utility\CacheHelper;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MonthlyDueListQuery
{
    const SORTABLE = ['billing_period', 'reference', 'total', 'penalty', 'discount', 'paid_amount', 'balance', 'status'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with(['user.household.property', 'user.resident']);

        $this->sort($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function find(int $id): Bill
    {
        return Bill::query()->monthlyDues()->with([
            'user.household.property.address',
            'user.resident',
            'items',
            'payments.paymentMethod',
        ])->findOrFail($id);
    }

    // ===== Options =====

    public function months(int $back = 12): array
    {
        return CacheHelper::remember('collections.due.options', ['months', $back], 3600, function () use ($back) {
            return PeriodHelper::recentMonths($back);
        });
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $query = Bill::query()->monthlyDues();

        $filters = $criteria['filters'] ?? [];

        $query->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->forMonth($filters['month'] ?? null);

        return $query;
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'billing_period',
            $direction
        )->orderByDesc('id');
    }
}
