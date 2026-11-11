<?php

namespace App\Queries\Accounting;

use App\Enums\AccountingPeriodStatus;
use App\Models\Account\AccountingPeriod;
use App\Models\JournalEntry\JournalEntry;
use App\Utility\PeriodHelper;
use Carbon\Carbon;

class PeriodListQuery
{
    // ===== Register =====

    public function months(?string $year = null): array
    {
        $year = $year ?: now()->format('Y');

        $rows = AccountingPeriod::with(['closedByAdmin', 'reopenedByAdmin'])
            ->get()
            ->keyBy('period');

        $counts = $this->entriesByMonth($year);

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $period = sprintf('%04d-%02d', (int) $year, $month);

            if ($period > PeriodHelper::current()) {
                continue;
            }

            $record = $rows->get($period);

            $months[] = [
                'period' => $period,
                'label'  => PeriodHelper::label($period),
                'short'  => PeriodHelper::shortLabel($period),
                'month'  => Carbon::createFromFormat('Y-m', $period)->format('F'),
                'record' => $record,
                'status' => $record && $record->status ? $record->status : AccountingPeriodStatus::OPEN(),
                'count'  => (int) ($counts[$period] ?? 0),
            ];
        }

        return array_reverse($months);
    }

    public function yearOptions(): array
    {
        $years = AccountingPeriod::query()->pluck('period')->all();

        $years[] = now()->format('Y');

        return PeriodHelper::years($years);
    }

    // ===== Helpers =====

    private function entriesByMonth(string $year): array
    {
        return JournalEntry::query()
            ->posted()
            ->whereYear('entry_date', $year)
            ->get(['entry_date'])
            ->groupBy(function (JournalEntry $entry) {
                return $entry->entry_date ? $entry->entry_date->format('Y-m') : '';
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->all();
    }
}
