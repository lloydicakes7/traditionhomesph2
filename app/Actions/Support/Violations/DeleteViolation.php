<?php

namespace App\Actions\Support\Violations;

use App\Models\Admin;
use App\Models\Violation;
use App\Services\ActivityLogService;

class DeleteViolation
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Violation $violation, Admin $by): void
    {
        $this->activity->log(
            $by,
            'deleted',
            'violation',
            "Deleted {$violation->type_label} violation against {$violation->resident_label}",
            ['violation_id' => $violation->id]
        );

        $violation->delete();
    }
}
