<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Payment;
use App\Services\Accounting\PaymentPostingService;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class PostPaymentJournal
{
    private ActivityLogService $activity;

    private PaymentPostingService $posting;

    public function __construct(ActivityLogService $activity, PaymentPostingService $posting)
    {
        $this->activity = $activity;
        $this->posting = $posting;
    }

    // ===== Handle =====

    public function handle(Payment $payment, array $data, Admin $by): Payment
    {
        if (! $payment->is_recorded) {
            throw ValidationException::withMessages([
                'payment' => 'Only a recorded payment can be posted to the journal.',
            ]);
        }

        if ($payment->journal_entry_id) {
            return $payment;
        }

        $account = Account::query()->cash()->find((int) ($data['account_id'] ?? 0));

        if (! $account) {
            throw ValidationException::withMessages([
                'account_id' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }

        $entry = $this->posting->post($payment, $account, $by->id);

        $this->activity->log(
            $by,
            'posted',
            'payment',
            "Posted payment {$payment->receipt_no} to journal entry {$entry->entry_no}",
            ['payment_id' => $payment->id, 'journal_entry_id' => $entry->id]
        );

        return $payment->fresh('journalEntry');
    }
}
