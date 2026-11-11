<?php

namespace App\Actions\Support\Requests;

use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;

class DeleteRequest
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'request',
            "Deleted {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        $request->delete();
    }
}
