<?php

namespace App\Actions\Collections\Invoices;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteInvoices
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = Bill::query()->invoices()
            ->whereIn('id', $ids)
            ->whereDoesntHave('payments')
            ->get();

        $deleted = DB::transaction(function () use ($removable) {
            return Bill::whereIn('id', $removable->pluck('id')->all())->delete();
        });

        foreach ($removable as $invoice) {
            $this->activity->log(
                $by,
                'deleted',
                'invoice',
                "Deleted invoice {$invoice->reference} for {$invoice->issued_to_name}",
                ['bill_id' => $invoice->id, 'user_id' => $invoice->user_id]
            );
        }

        return $deleted;
    }
}
