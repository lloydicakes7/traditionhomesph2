<?php

namespace App\Queries\Support\Requests;

use App\Models\Request as RequestModel;
use Illuminate\Database\Eloquent\Collection;

class PortalRequestQuery
{
    // ===== Rows =====

    /**
     * The requests one resident filed, newest first.
     *
     * The resident boundary is the query's own — the portal side of the policy
     * applied as the read itself — so another household's request is never
     * fetched and then filtered away.
     */
    public function all(?int $residentId): Collection
    {
        return RequestModel::query()
            ->forResident($residentId)
            ->with(['processedByAdmin', 'bill'])
            ->ordered()
            ->get();
    }

    /**
     * One request by id, for a portal dialog. Ownership is checked by the
     * caller's Policy before anything is read or written.
     */
    public function find(int $id): RequestModel
    {
        return RequestModel::query()->with(['resident', 'bill'])->findOrFail($id);
    }
}
