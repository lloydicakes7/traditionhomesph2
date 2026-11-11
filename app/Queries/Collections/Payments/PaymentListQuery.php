<?php

namespace App\Queries\Collections\Payments;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentListQuery
{
    const SORTABLE = ['paid_at', 'amount', 'reference_no'];

    // ===== Rows =====

    public function find(int $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function forPosting(int $id): Payment
    {
        return Payment::with([
            'bill.items.product.category',
            'paymentMethod',
            'user',
        ])->findOrFail($id);
    }

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with(['bill.user.household.property', 'paymentMethod']);

        $this->sort($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? 15);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function rows(array $criteria = []): Collection
    {
        return $this->query($criteria)
            ->with(['bill.user.household.property', 'paymentMethod'])
            ->ordered()
            ->get();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return Payment::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withMethod($filters['method'] ?? null)
            ->forMonth($filters['month'] ?? null);
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'paid_at',
            $direction
        )->orderByDesc('id');
    }
}
