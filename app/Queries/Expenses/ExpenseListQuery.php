<?php

namespace App\Queries\Expenses;

use App\Models\Expense\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseListQuery
{
    const SORTABLE = ['expense_date', 'reference', 'subtotal'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with(['items.category', 'supplier', 'createdByAdmin', 'journalEntry']);

        $this->sort($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function forPosting(int $id): Expense
    {
        return Expense::with(['items.category', 'journalEntry'])->findOrFail($id);
    }

    public function exportable(array $criteria = []): array
    {
        return $this->query($criteria)
            ->with(['items.category'])
            ->ordered()
            ->get()
            ->all();
    }

    // ===== Summary =====

    public function summary(): array
    {
        return [
            'month_total'    => (float) Expense::query()->forMonth(now()->format('Y-m'))->sum('subtotal'),
            'posted_total'   => (float) Expense::query()->whereNotNull('journal_entry_id')->sum('subtotal'),
            'unposted_total' => (float) Expense::query()->whereNull('journal_entry_id')->sum('subtotal'),
            'total_count'    => Expense::query()->count(),
        ];
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return Expense::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->inCategory($filters['category'] ?? null)
            ->forMonth($filters['month'] ?? null);
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'expense_date',
            $direction
        )->orderByDesc('id');
    }
}
