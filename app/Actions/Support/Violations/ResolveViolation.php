<?php

namespace App\Actions\Support\Violations;

use App\Enums\ViolationStatus;
use App\Models\Admin;
use App\Models\Violation;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class ResolveViolation
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Violation $violation, ?string $notes, Admin $by): Violation
    {
        if (! $violation->can_be_resolved) {
            throw ValidationException::withMessages([
                'resolutionNotes' => 'This violation has already been resolved — its status no longer changes.',
            ]);
        }

        $notes = trim((string) $notes);

        $violation->update([
            'status'               => ViolationStatus::RESOLVED(),
            'resolution_notes'     => $notes === '' ? null : $notes,
            'resolved_by_admin_id' => $by->id,
            'resolved_at'          => now(),
        ]);

        $this->activity->log(
            $by,
            'updated',
            'violation',
            "Resolved {$violation->type_label} violation against {$violation->resident_label}",
            ['violation_id' => $violation->id]
        );

        return $violation->fresh(['resident', 'issuedByAdmin', 'resolvedByAdmin']);
    }
}
