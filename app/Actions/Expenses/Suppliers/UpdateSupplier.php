<?php

namespace App\Actions\Expenses\Suppliers;

use App\Models\Admin;
use App\Models\Supplier;
use App\Services\ActivityLogService;

class UpdateSupplier
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Supplier $supplier, array $data, Admin $by): Supplier
    {
        $supplier->update([
            'name'        => $data['name'],
            'description' => ($data['description'] ?? null) ?: null,
        ]);

        $this->activity->log(
            $by,
            'updated',
            'supplier',
            "Updated supplier {$supplier->name}",
            ['supplier_id' => $supplier->id]
        );

        return $supplier;
    }
}
