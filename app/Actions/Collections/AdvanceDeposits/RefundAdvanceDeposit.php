<?php

namespace App\Actions\Collections\AdvanceDeposits;

use App\Enums\AdvanceDepositStatus;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Services\ActivityLogService;
use App\Utility\MoneyHelper;
use Illuminate\Support\Facades\DB;

class RefundAdvanceDeposit
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AdvanceDeposit $deposit, Admin $by): AdvanceDeposit
    {
        $returned = DB::transaction(function () use ($deposit) {
            $returned = (float) $deposit->balance;

            $deposit->update([
                'balance' => 0,
                'status'  => AdvanceDepositStatus::REFUNDED,
            ]);

            return $returned;
        });

        $this->activity->log(
            $by,
            'refunded',
            'advance_deposit',
            'Refunded ' . MoneyHelper::format($returned) . ' of the advance deposit for ' . $deposit->holder_name,
            ['advance_deposit_id' => $deposit->id, 'user_id' => $deposit->user_id]
        );

        return $deposit;
    }
}
