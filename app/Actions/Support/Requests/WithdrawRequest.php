<?php

namespace App\Actions\Support\Requests;

use App\Models\Request as RequestModel;

class WithdrawRequest
{
    // ===== Handle =====

    public function handle(RequestModel $request): void
    {
        $request->delete();
    }
}
