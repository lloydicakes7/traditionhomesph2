<?php

namespace App\Actions\Support\Violations;

use App\Actions\Support\Violations\Concerns\ViolationPayload;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Violation;
use App\Services\ActivityLogService;

class UpdateViolation
{
    use ViolationPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Violation $violation, array $data, Admin $by): Violation
    {
        $resident = Resident::findOrFail((int) $data['resident_id']);

        $violation->update(array_merge($this->payload($data), [
            'resident_id'   => $resident->id,
            'resident_name' => $resident->full_name,
        ]));

        $this->activity->log(
            $by,
            'updated',
            'violation',
            "Updated {$violation->type_label} violation against {$violation->resident_label}",
            ['violation_id' => $violation->id]
        );

        return $violation->fresh(['resident', 'issuedByAdmin', 'resolvedByAdmin']);
    }
}
