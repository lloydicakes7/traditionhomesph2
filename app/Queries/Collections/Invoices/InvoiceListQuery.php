<?php

namespace App\Queries\Collections\Invoices;

use App\Models\Bill\Bill;
use App\Models\User;
use App\Utility\CacheHelper;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceListQuery
{
    const SORTABLE = ['bill_date', 'reference', 'total', 'penalty', 'discount', 'paid_amount', 'balance', 'status'];

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

    public function count(array $criteria = []): int
    {
        return $this->query($criteria)->count();
    }

    // ===== Options =====

    public function months(int $back = 12): array
    {
        return CacheHelper::remember('collections.invoice.options', ['months', $back], 3600, function () use ($back) {
            return PeriodHelper::recentMonths($back);
        });
    }

    public function billableUsers(): Collection
    {
        return User::query()
            ->with(['resident', 'household.property'])
            ->get();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $query = Bill::query()->invoices();

        $filters = $criteria['filters'] ?? [];
        $search = (string) ($criteria['search'] ?? '');

        $query->search($search)
            ->withStatus($filters['status'] ?? null)
            ->forMonth($filters['month'] ?? null);

        return $query;
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'bill_date',
            $direction
        )->orderByDesc('id');
    }
}
