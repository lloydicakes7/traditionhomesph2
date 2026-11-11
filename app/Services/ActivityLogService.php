<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminActivity;

class ActivityLogService
{
    // ===== Operations =====

    public function log(
        Admin|int $by,
        string $action,
        string $module,
        string $description,
        array $metadata = []
    ): void {
        AdminActivity::create([
            'admin_id'    => $by instanceof Admin ? $by->id : $by,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $this->request() ? $this->request()->ip() : null,
            'user_agent'  => $this->request() ? $this->request()->userAgent() : null,
            'metadata'    => $metadata === [] ? null : $metadata,
        ]);
    }

    // ===== Helpers =====

    private function request()
    {
        return request();
    }
}
