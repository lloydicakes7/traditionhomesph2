<?php

namespace App\Actions\Support\Requests;

use App\Actions\Support\Requests\Concerns\DecidesRequest;
use App\Enums\RequestStatus;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApproveRequest
{
    use DecidesRequest;

    private NotificationService $notifications;

    private ActivityLogService $activity;

    public function __construct(NotificationService $notifications, ActivityLogService $activity)
    {
        $this->notifications = $notifications;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(RequestModel $request, array $data, Admin $by): RequestModel
    {
        return $this->decide(
            $request,
            RequestStatus::APPROVED(),
            $this->remarks($data),
            $by,
            $this->claimingAt($data)
        );
    }

    // ===== Helpers =====

    private function remarks(array $data): ?string
    {
        $remarks = trim((string) ($data['remarks'] ?? ''));

        return $remarks === '' ? null : $remarks;
    }

    /**
     * The moment the homeowner may claim the document: required, and never in
     * the past. The dialog validates the same rule; this is the write's own
     * guard, so an approval can never be recorded without a schedule.
     */
    private function claimingAt(array $data): Carbon
    {
        $value = trim((string) ($data['claiming_at'] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([
                'claimingAt' => 'Choose the date and time the homeowner can claim the document.',
            ]);
        }

        try {
            $claimingAt = Carbon::createFromFormat('Y-m-d\TH:i', $value);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'claimingAt' => 'The claiming date and time is not valid.',
            ]);
        }

        if ($claimingAt->lt(now()->startOfMinute())) {
            throw ValidationException::withMessages([
                'claimingAt' => 'The claiming date and time cannot be in the past.',
            ]);
        }

        return $claimingAt->startOfMinute();
    }
}
