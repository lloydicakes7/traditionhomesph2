<?php

namespace App\Actions\Accounting\Periods;

use App\Enums\AccountingPeriodStatus;
use App\Models\Account\AccountingPeriod;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\PeriodHelper;
use Illuminate\Validation\ValidationException;

class ClosePeriod
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $period, Admin $by, ?string $notes = null): AccountingPeriod
    {
        if ($period > PeriodHelper::current()) {
            throw ValidationException::withMessages([
                'period' => 'You cannot close a month that has not happened yet.',
            ]);
        }

        $row = AccountingPeriod::firstOrNew(['period' => $period]);

        if ($row->exists && $row->isClosed()) {
            throw ValidationException::withMessages([
                'period' => PeriodHelper::label($period) . ' is already closed.',
            ]);
        }

        $row->fill([
            'status'               => AccountingPeriodStatus::CLOSED,
            'closed_at'            => now(),
            'closed_by_admin_id'   => $by->id,
            'reopened_at'          => null,
            'reopened_by_admin_id' => null,
            'notes'                => filled($notes) ? $notes : $row->notes,
        ])->save();

        $this->activity->log(
            $by,
            'updated',
            'Period Close',
            'Closed the books for ' . PeriodHelper::label($period),
            ['period' => $period]
        );

        return $row;
    }
}
