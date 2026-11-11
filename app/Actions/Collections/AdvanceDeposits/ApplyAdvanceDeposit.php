<?php

namespace App\Actions\Collections\AdvanceDeposits;

use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class ApplyAdvanceDeposit
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AdvanceDeposit $deposit, Bill $bill, string $amount, Admin $by): ?Payment
    {
        $amount = bcadd($amount, '0', 2);

        if (! $deposit->is_open || (int) $bill->user_id !== (int) $deposit->user_id) {
            return null;
        }

        if (bccomp($amount, '0.01', 2) < 0
            || bccomp($amount, (string) $bill->balance, 2) > 0
            || bccomp($amount, (string) $deposit->balance, 2) > 0) {
            return null;
        }

        $payment = DB::transaction(function () use ($deposit, $bill, $amount, $by) {
            $payment = $bill->settle([
                'amount'               => $amount,
                'advance_deposit_id'   => $deposit->id,
                'payment_method_id'    => null,
                'reference_no'         => 'AD-' . $deposit->id,
                'paid_at'              => now(),
                'notes'                => 'Applied from advance deposit #' . $deposit->id,
                'received_by_admin_id' => $by->id,
            ]);

            $deposit->applyCredit((float) $amount);

            return $payment;
        });

        $this->activity->log(
            $by,
            'received',
            'payment',
            "Received {$payment->amount_display} for {$bill->reference}",
            ['payment_id' => $payment->id, 'bill_id' => $bill->id]
        );

        return $payment;
    }
}
