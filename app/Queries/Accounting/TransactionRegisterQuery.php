<?php

namespace App\Queries\Accounting;

use App\Enums\JournalEntryStatus;
use App\Models\JournalEntry\JournalLine;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRegisterQuery
{
    const SORTABLE = ['entry_date', 'amount', 'account'];

    // ===== Rows =====

    public function paginate(array $criteria = [], string $sortField = 'entry_date', string $sortDirection = 'desc'): LengthAwarePaginator
    {
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';
        $field = in_array($sortField, self::SORTABLE, true) ? $sortField : 'entry_date';

        $column = [
            'amount'  => 'journal_lines.amount',
            'account' => 'journal_lines.account_id',
        ][$field] ?? 'journal_entries.entry_date';

        return $this->query($criteria)
            ->select('journal_lines.*')
            ->with(['account', 'entry.postedByAdmin'])
            ->orderBy($column, $direction)
            ->orderBy('journal_entries.id', $direction)
            ->orderBy('journal_lines.id')
            ->paginate($criteria['perPage'] ?? 25);
    }

    public function exportable(array $criteria = []): array
    {
        return $this->query($criteria)
            ->select('journal_lines.*')
            ->with(['account', 'entry'])
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->orderBy('journal_lines.id')
            ->get()
            ->all();
    }

    // ===== Totals =====

    public function totals(array $criteria = []): array
    {
        $rows = $this->query($criteria)
            ->selectRaw('journal_lines.side as side')
            ->selectRaw('SUM(journal_lines.amount) as total')
            ->selectRaw('COUNT(*) as movements')
            ->groupBy('journal_lines.side')
            ->get();

        $debits = 0.0;
        $credits = 0.0;
        $count = 0;

        foreach ($rows as $row) {
            $count += (int) $row->movements;

            if ($row->side === JournalLine::SIDE_DEBIT) {
                $debits = round((float) $row->total, 2);
            } else {
                $credits = round((float) $row->total, 2);
            }
        }

        return ['debits' => $debits, 'credits' => $credits, 'count' => $count];
    }

    // ===== Options =====

    public function months(): array
    {
        return PeriodHelper::recentMonths();
    }

    // ===== Helpers =====

    private function query(array $criteria)
    {
        $filters = $criteria['filters'] ?? [];
        $search = trim((string) ($criteria['search'] ?? ''));
        $source = (string) ($filters['source'] ?? '');
        $accountId = (string) ($filters['account'] ?? '');
        $month = (string) ($filters['month'] ?? '');

        return JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_entries.status', JournalEntryStatus::POSTED)
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';

                $query->where(function ($query) use ($like) {
                    $query->where('journal_entries.entry_no', 'like', $like)
                        ->orWhere('journal_entries.reference_no', 'like', $like)
                        ->orWhere('journal_entries.memo', 'like', $like)
                        ->orWhere('journal_lines.memo', 'like', $like)
                        ->orWhere('journal_lines.account_name', 'like', $like);
                });
            })
            ->when($source !== '', function ($query) use ($source) {
                $query->where('journal_entries.source_type', $source);
            })
            ->when($accountId !== '', function ($query) use ($accountId) {
                $query->where('journal_lines.account_id', (int) $accountId);
            })
            ->when($month !== '', function ($query) use ($month) {
                $query->whereYear('journal_entries.entry_date', (int) substr($month, 0, 4))
                    ->whereMonth('journal_entries.entry_date', (int) substr($month, 5, 2));
            });
    }
}
