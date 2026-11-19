<?php

namespace App\Actions\Collections\Payments;

use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
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

            if (! empty($data['advance_deposit_id'])) {
                return $this->settleFromDeposit($bill, $data, $by);
            }

            $proof = $data['proof'] ?? null;

            return $bill->settle([
                'amount'               => $data['amount'],
                'payment_method_id'    => ($data['payment_method_id'] ?? null) ?: null,
                'reference_no'         => ($data['reference_no'] ?? null) ?: null,
                'proof_path'           => $proof ? StorageHelper::storePhoto($proof, Payment::PROOF_DIRECTORY) : null,
                'paid_at'              => filled($data['paid_at'] ?? null) ? $data['paid_at'] : now(),
                'notes'                => ($data['notes'] ?? null) ?: null,
                'received_by_admin_id' => $by->id,
                'status'               => PaymentStatus::RECORDED,
            ]);
        });

        $source = $payment->advance_deposit_id
            ? ' from advance deposit #' . $payment->advance_deposit_id
            : '';

        $this->activity->log(
            $by,
            'received',
            'payment',
            "Received {$payment->amount_display} for {$bill->reference}{$source}",
            ['payment_id' => $payment->id, 'bill_id' => $bill->id]
        );

        return $payment;
    }

    // ===== Helpers =====

    private function settleFromDeposit(Bill $bill, array $data, Admin $by): Payment
    {
        $deposit = AdvanceDeposit::query()->lockForUpdate()->find((int) $data['advance_deposit_id']);

        if (! $deposit || (int) $deposit->user_id !== (int) $bill->user_id || ! $deposit->is_open) {
            throw ValidationException::withMessages([
                'advance_deposit_id' => 'That advance deposit is not available for this homeowner.',
            ]);
        }

        $amount = bcadd((string) ($data['amount'] ?? '0'), '0', 2);

        if (bccomp($amount, '0.01', 2) < 0 || bccomp($amount, (string) $bill->balance, 2) > 0) {
            throw ValidationException::withMessages([
                'amount' => 'The amount must be more than zero and no more than the bill balance.',
            ]);
        }

        if (bccomp($amount, (string) $deposit->balance, 2) > 0) {
            throw ValidationException::withMessages([
                'amount' => 'The amount is more than the credit this deposit holds.',
            ]);
        }

        $payment = $bill->settle([
            'amount'               => $amount,
            'advance_deposit_id'   => $deposit->id,
            'payment_method_id'    => null,
            'reference_no'         => 'AD-' . $deposit->id,
            'paid_at'              => now(),
            'notes'                => ($data['notes'] ?? null) ?: 'Applied from advance deposit #' . $deposit->id,
            'received_by_admin_id' => $by->id,
            'status'               => PaymentStatus::RECORDED,
        ]);

        $deposit->applyCredit((float) $amount);

        return $payment;
    }

    private function guardAgainstWaitingReceipt(Bill $bill): void
    {
        $awaiting = Payment::query()->waitingForBill($bill->id)->first();

        if (! $awaiting) {
            return;
        }

        throw ValidationException::withMessages([
            'bill' => 'A payment of ' . $awaiting->amount_display . ' for ' . $bill->reference
                . ' is already waiting for approval. Approve it on the Payments page instead of'
                . ' recording the same money again.',
        ]);
    }
}
