<?php

namespace App\Queries\Accounting;

use App\Models\Account\AccountTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransferListQuery
{
    const SORTABLE = ['reference', 'transfer_date', 'amount'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)
            ->with(['fromAccount', 'toAccount', 'journalEntry', 'createdByAdmin']);

        $this->sort($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? 20);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function find(int $id): AccountTransfer
    {
        return AccountTransfer::with(['fromAccount', 'toAccount', 'journalEntry', 'createdByAdmin'])
            ->findOrFail($id);
    }

    public function exportable(array $criteria = []): array
    {
        return $this->query($criteria)
            ->with(['fromAccount', 'toAccount'])
            ->ordered()
            ->get()
            ->all();
    }

    // ===== Totals =====

    public function totals(array $criteria = []): array
    {
        $query = $this->query($criteria);

        return [
            'count'  => (int) $query->count(),
            'amount' => round((float) $query->sum('amount'), 2),
        ];
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return AccountTransfer::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->when(! empty($filters['month']), function ($query) use ($filters) {
                $query->ofMonth($filters['month']);
            })
            ->when(! empty($filters['account']), function ($query) use ($filters) {
                $query->where(function ($query) use ($filters) {
                    $query->where('from_account_id', (int) $filters['account'])
                        ->orWhere('to_account_id', (int) $filters['account']);
                });
            });
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'transfer_date',
            $direction
        )->orderByDesc('id');
    }
}
