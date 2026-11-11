<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Enums\BillStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CancelMonthlyDue
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Bill $due, Admin $by): Bill
    {
        $due = DB::transaction(function () use ($due) {
            $due->update(['status' => BillStatus::CANCELLED]);

            $due->recalculate();

            return $due;
        });

        $this->activity->log(
            $by,
            'cancelled',
            'monthly_due',
            'Cancelled monthly due ' . $due->reference . ' for ' . ($due->property?->block_lot_label ?? '—'),
            ['bill_id' => $due->id]
        );

        return $due;
    }
}
