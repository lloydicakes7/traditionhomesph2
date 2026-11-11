<?php

namespace App\Actions\Support\Violations;

use App\Models\Violation;

class AcknowledgeViolation
{
    // ===== Handle =====

    public function handle(Violation $violation): Violation
    {
        if ($violation->is_acknowledged) {
            return $violation;
        }

        $violation->update(['acknowledged_at' => now()]);

        return $violation;
    }
}
