<?php

namespace App\Actions\Collections\Payments;

use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordPayment
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Bill $bill, array $data, Admin $by): Payment
    {
        $payment = DB::transaction(function () use ($bill, $data, $by) {
            $this->guardAgainstWaitingReceipt($bill);

            $proof = $data['proof'] ?? null;

            $payment = $bill->settle([
                'amount'               => $data['amount'],
                'payment_method_id'    => ($data['payment_method_id'] ?? null) ?: null,
                'reference_no'         => ($data['reference_no'] ?? null) ?: null,
                'proof_path'           => $proof ? StorageHelper::storePhoto($proof, Payment::PROOF_DIRECTORY) : null,
                'paid_at'              => filled($data['paid_at'] ?? null) ? $data['paid_at'] : now(),
                'notes'                => ($data['notes'] ?? null) ?: null,
                'advance_deposit_id'   => $data['advance_deposit_id'] ?? null,
                'received_by_admin_id' => $by->id,
                'status'               => PaymentStatus::RECORDED,
            ]);

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

    // ===== Helpers =====

    private function guardAgainstWaitingReceipt(Bill $bill): void
    {
        $awaiting = Payment::query()->waitingForBill($bill->id)->first();

        if (! $awaiting) {
            return;
        }

        throw ValidationException::withMessages([
            'bill' => 'A payment of ' . $awaiting->amount_display . ' for ' . $bill->reference
                . ' is already waiting for approval — approve it on the Payments page instead of'
                . ' recording the same money again.',
        ]);
    }
}
