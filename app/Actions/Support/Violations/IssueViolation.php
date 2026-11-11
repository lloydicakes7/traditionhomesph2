<?php

namespace App\Actions\Support\Violations;

use App\Actions\Support\Violations\Concerns\ViolationPayload;
use App\Enums\ViolationStatus;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Violation;
use App\Notifications\ViolationIssuedNotification;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;
use Throwable;

class IssueViolation
{
    use ViolationPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Violation
    {
        $resident = Resident::findOrFail((int) $data['resident_id']);

        $violation = Violation::create(array_merge($this->payload($data), [
            'resident_id'        => $resident->id,
            'resident_name'      => $resident->full_name,
            'issued_by_admin_id' => $by->id,
            'issued_at'          => now(),
            'status'             => ViolationStatus::ACTIVE(),
        ]));

        $this->activity->log(
            $by,
            'created',
            'violation',
            "Issued {$violation->type_label} violation against {$violation->resident_label}",
            ['violation_id' => $violation->id]
        );

        $this->notifyHomeowner($violation);

        return $violation->fresh(['resident', 'issuedByAdmin']);
    }

    // ===== Helpers =====

    /**
     * A delivery that fails does not undo the violation: it is logged and the
     * record stands, the same way a statement that cannot be mailed is.
     */
    private function notifyHomeowner(Violation $violation): void
    {
        $account = optional($violation->resident)->user;

        if (! $account) {
            return;
        }

        try {
            $account->notify(new ViolationIssuedNotification($violation));
        } catch (Throwable $exception) {
            Log::warning('Violation notice could not be emailed.', [
                'violation_id' => $violation->id,
                'user_id'      => $account->id,
                'error'        => $exception->getMessage(),
            ]);
        }
    }
}
