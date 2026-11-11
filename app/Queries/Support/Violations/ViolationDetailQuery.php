<?php

namespace App\Queries\Support\Violations;

use App\Models\Violation;

class ViolationDetailQuery
{
    // ===== Queries =====

    public function find(int $id): Violation
    {
        return Violation::with(['resident', 'issuedByAdmin', 'resolvedByAdmin'])->findOrFail($id);
    }
}
