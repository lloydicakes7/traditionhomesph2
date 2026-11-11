<?php

namespace App\Actions\Collections\Payments;

use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Payment;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class ApprovePayment
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Payment $payment, Admin $by): Payment
    {
        if (! $payment->is_pending) {
            return $payment;
        }

        $payment = DB::transaction(function () use ($payment, $by) {
            $payment->update([
                'status'               => PaymentStatus::RECORDED,
                'receipt_no'           => $payment->receipt_no ?: Payment::nextReceiptNo(),
                'received_by_admin_id' => $by->id,
            ]);

            $payment->bill?->recalculate();

            return $payment;
        });

        $this->activity->log(
            $by,
            'received',
            'payment',
            "Approved the portal payment of {$payment->amount_display} for {$payment->bill_reference}",
            ['payment_id' => $payment->id, 'bill_id' => $payment->bill_id]
        );

        return $payment;
    }
}
