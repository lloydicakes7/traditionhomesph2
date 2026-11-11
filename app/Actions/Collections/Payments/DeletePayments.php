<?php

namespace App\Actions\Collections\Payments;

use App\Enums\AdvanceDepositStatus;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class DeletePayments
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $payments = Payment::with(['bill', 'advanceDeposit'])
            ->whereIn('id', $ids)
            ->get();

        $deleted = DB::transaction(function () use ($payments, $by) {
            foreach ($payments as $payment) {
                JournalEntry::voidFor($payment);

                StorageHelper::deletePhoto($payment->proof_path);

                $this->restoreDeposit($payment);
            }

            $deleted = Payment::whereIn('id', $payments->pluck('id')->all())->delete();

            foreach ($payments as $payment) {
                $payment->bill?->recalculate();
            }

            return $deleted;
        });

        foreach ($payments as $payment) {
            $this->activity->log(
                $by,
                'deleted',
                'payment',
                "Deleted payment of {$payment->amount_display} for {$payment->bill_reference}",
                ['payment_id' => $payment->id, 'bill_id' => $payment->bill_id]
            );
        }

        return $deleted;
    }

    // ===== Helpers =====

    private function restoreDeposit(Payment $payment): void
    {
        $deposit = $payment->advanceDeposit;

        if (! $deposit || $deposit->status === AdvanceDepositStatus::REFUNDED) {
            return;
        }

        $deposit->restoreCredit((float) $payment->amount);
    }
}
