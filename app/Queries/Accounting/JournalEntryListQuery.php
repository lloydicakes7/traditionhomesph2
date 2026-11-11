<?php

namespace App\Queries\Accounting;

use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JournalEntryListQuery
{
    const SORTABLE = ['entry_no', 'entry_date'];

    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        $query = $this->query($criteria)->with(['lines.account', 'postedByAdmin']);

        $this->sort($query, $criteria);

        return $query->paginate($criteria['perPage'] ?? 20);
    }

    public function ids(array $criteria = []): array
    {
        return $this->query($criteria)->pluck('id')->all();
    }

    public function find(int $id): JournalEntry
    {
        return JournalEntry::with(['lines.account', 'postedByAdmin'])->findOrFail($id);
    }

    public function linesFor(JournalEntry $entry)
    {
        return $entry->lines()
            ->with('account')
            ->orderByRaw('CASE WHEN side = ? THEN 0 ELSE 1 END', [JournalLine::SIDE_DEBIT])
            ->orderByDesc('amount')
            ->get();
    }

    public function exportable(array $criteria = []): array
    {
        return $this->query($criteria)
            ->with(['lines.account', 'postedByAdmin'])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get()
            ->all();
    }

    // ===== Totals =====

    /**
     * What the filtered entries come to. Every entry is balanced, so one side
     * is the whole amount.
     *
     * @return array{amount: string}
     */
    public function totals(array $criteria = []): array
    {
        $total = JournalLine::query()
            ->whereIn('journal_entry_id', $this->ids($criteria))
            ->where('side', JournalLine::SIDE_DEBIT)
            ->sum('amount');

        return [
            'amount' => bcadd((string) ($total ?: '0'), '0', 2),
        ];
    }

    // ===== Options =====

    public function periods(): array
    {
        $years = JournalEntry::query()
            ->select('entry_date')
            ->distinct()
            ->pluck('entry_date')
            ->map(function ($date) {
                return substr((string) $date, 0, 4);
            })
            ->all();

        return ['' => 'All Time'] + PeriodHelper::years($years);
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];

        return JournalEntry::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->when(! empty($filters['source']), function ($query) use ($filters) {
                $query->where('source_type', $filters['source']);
            })
            ->when(! empty($filters['classification']), function ($query) use ($filters) {
                $query->where('classification', $filters['classification']);
            })
            ->when(! empty($filters['period']), function ($query) use ($filters) {
                $query->whereYear('entry_date', (int) $filters['period']);
            });
    }

    private function sort($query, array $criteria): void
    {
        $field = $criteria['sort'] ?? '';
        $direction = ($criteria['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy(
            in_array($field, self::SORTABLE, true) ? $field : 'entry_date',
            $direction
        )->orderByDesc('id');
    }
}
