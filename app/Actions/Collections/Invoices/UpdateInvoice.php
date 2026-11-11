<?php

namespace App\Actions\Collections\Invoices;

use App\Actions\Collections\Invoices\Concerns\ResolvesInvoiceLines;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateInvoice
{
    use ResolvesInvoiceLines;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Bill $invoice, array $data, Admin $by): Bill
    {
        $lines = $this->resolveLines($data['items'] ?? []);

        $invoice = DB::transaction(function () use ($invoice, $data, $lines) {
            $invoice->items()->delete();

            $invoice->items()->createMany($lines);

            $invoice->update([
                'billed_to_name' => ($data['billed_to_name'] ?? null) ?: $invoice->issued_to_name,
                'bill_date'      => $data['bill_date'],
                'due_date'       => ($data['due_date'] ?? null) ?: null,
                'discount'       => $data['discount'] ?? 0,
                'notes'          => ($data['notes'] ?? null) ?: null,
            ]);

            $invoice->recalculate();

            return $invoice->fresh(['items']);
        });

        $this->activity->log(
            $by,
            'updated',
            'invoice',
            "Updated invoice {$invoice->reference} ({$invoice->total_display})",
            ['bill_id' => $invoice->id, 'user_id' => $invoice->user_id]
        );

        return $invoice;
    }
}
