<?php

namespace App\Actions\Collections\Invoices;

use App\Enums\BillStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CancelInvoice
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Bill $invoice, Admin $by): Bill
    {
        $invoice = DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => BillStatus::CANCELLED]);

            $invoice->recalculate();

            return $invoice;
        });

        $this->activity->log(
            $by,
            'cancelled',
            'invoice',
            "Cancelled invoice {$invoice->reference}",
            ['bill_id' => $invoice->id, 'user_id' => $invoice->user_id]
        );

        return $invoice;
    }
}
