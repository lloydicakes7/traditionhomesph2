<?php

namespace App\Queries\Support\Violations;

use App\Models\Violation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ViolationListQuery
{
    const PER_PAGE = 15;

    const SORTABLE = [
        'occurred_on'    => 'occurred_on',
        'violation_type' => 'violation_type',
        'status'         => 'status',
        'location'       => 'location',
        'resident_name'  => 'resident_name',
        'member'         => 'resident_name',
    ];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return $this->query($criteria)
            ->with(['resident', 'issuedByAdmin', 'resolvedByAdmin'])
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

    public function activeCount(): int
    {
        return Violation::query()->active()->count();
    }

    public function recentActive(int $limit = 5): Collection
    {
        return Violation::query()
            ->active()
            ->with('resident')
            ->ordered()
            ->limit($limit)
            ->get();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return Violation::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->withStatus($filters['status'] ?? null)
            ->withType($filters['type'] ?? null);
    }

    private function sortColumn(?string $field): string
    {
        return self::SORTABLE[$field] ?? 'occurred_on';
    }

    private function direction(?string $direction): string
    {
        return $direction === 'asc' ? 'asc' : 'desc';
    }
}
