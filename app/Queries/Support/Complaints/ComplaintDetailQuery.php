<?php

namespace App\Queries\Support\Complaints;

use App\Models\Complaint;

class ComplaintDetailQuery
{
    // ===== Queries =====

    public function find(int $id): Complaint
    {
        return Complaint::with(['resident', 'filedByAdmin', 'respondedByAdmin'])->findOrFail($id);
    }
}
