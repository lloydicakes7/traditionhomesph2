<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Utility\PeriodHelper;
use Illuminate\Support\Facades\DB;

class ApplyAdvanceDepositsToDues
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $month, Admin $by): int
    {
        $drawn = DB::transaction(function () use ($month, $by) {
            $drawn = 0;

            $dues = Bill::query()->monthlyDues()
                ->where('billing_period', $month)
                ->where('balance', '>', 0)
                ->orderBy('id')
                ->get();

            foreach ($dues as $due) {
                $drawn += $this->drawFor($due, $by->id);
            }

            return $drawn;
        });

        if ($drawn > 0) {
            $this->activity->log(
                $by,
                'applied',
                'monthly_due',
                'Applied advance deposits to ' . $drawn . ' monthly due invoice(s) for ' . PeriodHelper::label($month),
                ['month' => $month, 'payments' => $drawn]
            );
        }

        return $drawn;
    }

    // ===== Helpers =====

    private function drawFor(Bill $due, int $adminId): int
    {
        if (Payment::query()->waitingForBill($due->id)->exists()) {
            return 0;
        }

        $drawn = 0;
        $deposit = AdvanceDeposit::query()->oldestOpenFor($due->user_id)->first();

        while ($deposit && (float) $due->balance > 0) {
            $amount = round(min((float) $deposit->balance, (float) $due->balance), 2);

            if ($amount <= 0) {
                break;
            }

            $due->settle([
                'amount'               => $amount,
                'advance_deposit_id'   => $deposit->id,
                'payment_method_id'    => null,
                'reference_no'         => 'AD-' . $deposit->id,
                'paid_at'              => now(),
                'notes'                => 'Applied from advance deposit #' . $deposit->id,
                'received_by_admin_id' => $adminId,
            ]);

            $deposit->applyCredit($amount);

            $due->refresh();
            $drawn++;

            $deposit = AdvanceDeposit::query()->oldestOpenFor($due->user_id)->first();
        }

        return $drawn;
    }
}
