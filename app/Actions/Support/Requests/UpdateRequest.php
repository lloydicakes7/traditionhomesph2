<?php

namespace App\Actions\Support\Requests;

use App\Actions\Support\Requests\Concerns\RequestPayload;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class UpdateRequest
{
    use RequestPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, array $data, Admin $by): RequestModel
    {
        if (! $request->is_open) {
            throw ValidationException::withMessages([
                'form.type' => 'This request has already been decided — it can no longer be edited.',
            ]);
        }

        $this->ensureMembershipProfileReady($data, $request->resident_id);

        $request->update($this->payload($data));

        $this->activity->log(
            $by,
            'updated',
            'request',
            "Updated {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        return $request->fresh(['resident', 'processedByAdmin']);
    }
}
