<?php

namespace App\Queries\Support\Requests;

use App\Models\Request as RequestModel;

class RequestDetailQuery
{
    // ===== Queries =====

    public function find(int $id): RequestModel
    {
        return RequestModel::with(['resident', 'processedByAdmin', 'bill'])->findOrFail($id);
    }
}
