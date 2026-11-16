<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Models\Admin;
use App\Models\Payment;
use App\Services\Accounting\PaymentPostingService;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

/**
 * Post a recorded receipt to the books.
 *
 * The entry is the one accounting confirmed in the posting dialog, starting
 * from the draft the system wrote for the receipt. The payment itself is the
 * source: the service stamps it on the entry and links the two, and the money
 * side must still land in a cash account.
 */
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

    public function handle(Payment $payment, array $entry, Admin $by): Payment
    {
        if (! $payment->is_recorded) {
            throw ValidationException::withMessages([
                'payment' => 'Only a recorded payment can be posted to the journal.',
            ]);
        }

        if ($payment->journal_entry_id) {
            return $payment;
        }

        $posted = $this->posting->post($payment, $entry, $by->id);

        $this->activity->log(
            $by,
            'posted',
            'payment',
            "Posted payment {$payment->receipt_no} to journal entry {$posted->entry_no}",
            ['payment_id' => $payment->id, 'journal_entry_id' => $posted->id]
        );

        return $payment->fresh('journalEntry');
    }
}
