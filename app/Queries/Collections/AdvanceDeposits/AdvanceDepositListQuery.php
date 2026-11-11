<?php

namespace App\Queries\Collections\AdvanceDeposits;

use App\Models\AdvanceDeposit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdvanceDepositListQuery
{
    const SORTABLE = ['deposited_at', 'amount', 'balance', 'status'];

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

    public function find(int $id): AdvanceDeposit
    {
        return AdvanceDeposit::with([
            'user.household.property',
            'user.resident',
            'payments.bill',
        ])->findOrFail($id);
    }

    // ===== Options =====

    public function holderOptions(): array
    {
        return User::query()
            ->with(['resident', 'household.property'])
            ->orderBy('id')
            ->get()
            ->mapWithKeys(function (User $user) {
                $unit = $user->household?->property?->block_lot_label;

                return [
                    $user->id => $user->name . ($unit && $unit !== '—' ? ' · ' . $unit : ''),
                ];
            })
            ->all();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return AdvanceDeposit::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null);
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'deposited_at',
            $direction
        )->orderByDesc('id');
    }
}
