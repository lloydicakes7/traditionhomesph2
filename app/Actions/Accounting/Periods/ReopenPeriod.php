<?php

namespace App\Actions\Accounting\Periods;

use App\Enums\AccountingPeriodStatus;
use App\Models\Account\AccountingPeriod;
use App\Models\Admin;
use App\Services\ActivityLogService;
use App\Utility\PeriodHelper;
use Illuminate\Validation\ValidationException;

class ReopenPeriod
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $period, Admin $by): AccountingPeriod
    {
        $row = AccountingPeriod::query()->where('period', $period)->first();

        if (! $row || ! $row->isClosed()) {
            throw ValidationException::withMessages([
                'period' => PeriodHelper::label($period) . ' is not closed.',
            ]);
        }

        $row->update([
            'status'               => AccountingPeriodStatus::OPEN,
            'reopened_at'          => now(),
            'reopened_by_admin_id' => $by->id,
        ]);

        $this->activity->log(
            $by,
            'updated',
            'Period Close',
            'Reopened the books for ' . PeriodHelper::label($period),
            ['period' => $period]
        );

        return $row;
    }
}
