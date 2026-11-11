<?php

namespace App\Queries\Support\Complaints;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Collection;

class PortalComplaintQuery
{
    // ===== Rows =====

    /**
     * The complaints one resident filed, newest first.
     *
     * The resident boundary is the query's own — the portal side of the policy
     * applied as the read itself — so another household's complaint is never
     * fetched and then filtered away.
     */
    public function all(?int $residentId): Collection
    {
        return Complaint::query()
            ->forResident($residentId)
            ->with('respondedByAdmin')
            ->ordered()
            ->get();
    }
}
