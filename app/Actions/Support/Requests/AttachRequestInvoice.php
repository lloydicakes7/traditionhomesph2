<?php

namespace App\Actions\Support\Requests;

use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;

class AttachRequestInvoice
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, int $billId, Admin $by): RequestModel
    {
        if ($request->bill_id !== null) {
            return $request->fresh(['resident', 'processedByAdmin', 'bill']);
        }

        $request->update(['bill_id' => $billId]);

        $this->activity->log(
            $by,
            'updated',
            'request',
            "Linked invoice to {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        return $request->fresh(['resident', 'processedByAdmin', 'bill']);
    }
}
