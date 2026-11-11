<?php

namespace App\Actions\Support\Requests\Concerns;

use App\Enums\RequestStatus;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Notifications\RequestDecidedNotification;
use Illuminate\Validation\ValidationException;

trait DecidesRequest
{
    // ===== Decision =====

    /**
     * Write one decision onto a request.
     *
     * The status, the officer and the moment are written together, and only a
     * move the request's own status allows is accepted. Approving dates the
     * certification the office will print and hand over.
     */
    protected function decide(RequestModel $request, RequestStatus $status, ?string $remarks, Admin $by): RequestModel
    {
        $this->refuseUnlessPending($request);

        if (! in_array($status->value, $request->status->canMoveTo(), true)) {
            throw ValidationException::withMessages([
                'remarks' => 'A request that is ' . $request->status->label() . ' cannot be moved to ' . $status->label() . '.',
            ]);
        }

        $attributes = [
            'status'                => $status,
            'remarks'               => $remarks,
            'processed_by_admin_id' => $by->id,
            'processed_at'          => now(),
        ];

        if ($status->isApproved()) {
            $attributes['document_expires_at'] = now()->addDays(RequestModel::DOCUMENT_VALID_DAYS);
        }

        $request->update($attributes);

        $this->activity->log(
            $by,
            'updated',
            'request',
            ucfirst($status->value) . " {$request->type_label} request for {$request->resident_label}",
            ['request_id' => $request->id]
        );

        $request = $request->fresh(['resident', 'processedByAdmin']);

        $this->notifications->toHomeowner(
            $request->resident ? $request->resident->user : null,
            new RequestDecidedNotification($request)
        );

        return $request;
    }

    // ===== Helpers =====

    private function refuseUnlessPending(RequestModel $request): void
    {
        if ($request->is_open) {
            return;
        }

        throw ValidationException::withMessages([
            'remarks' => 'This request has already been ' . ($request->status ? $request->status->label() : 'decided')
                . ' — it can no longer be decided.',
        ]);
    }
}
