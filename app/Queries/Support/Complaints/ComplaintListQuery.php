<?php

namespace App\Queries\Support\Complaints;

use App\Models\Complaint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ComplaintListQuery
{
    const PER_PAGE = 15;

    const SORTABLE = [
        'created_at' => 'created_at',
        'category'   => 'category',
        'subject'    => 'subject',
        'status'     => 'status',
    ];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return $this->query($criteria)
            ->with(['resident', 'filedByAdmin', 'respondedByAdmin'])
            ->orderBy($this->sortColumn($criteria['sort'] ?? null), $this->direction($criteria['direction'] ?? null))
            ->orderByDesc('id')
            ->paginate($criteria['perPage'] ?? self::PER_PAGE);
    }

    /**
     * The ids the current filters match — what "select all pages" selects.
     *
     * @return array<int, int>
     */
    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    // ===== Dashboard =====

    public function openCount(): int
    {
        return Complaint::query()->open()->count();
    }

    public function recentOpen(int $limit = 5): Collection
    {
        return Complaint::query()
            ->open()
            ->with('resident')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return Complaint::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->withCategory($filters['category'] ?? null);
    }

    private function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? 'created_at';
    }

    private function direction(?string $direction): string
    {
        return $direction === 'asc' ? 'asc' : 'desc';
    }
}
