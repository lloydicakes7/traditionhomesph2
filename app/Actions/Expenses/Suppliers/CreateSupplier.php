<?php

namespace App\Actions\Expenses\Suppliers;

use App\Models\Admin;
use App\Models\Supplier;
use App\Services\ActivityLogService;

class CreateSupplier
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Supplier
    {
        $supplier = Supplier::create([
            'name'        => $data['name'],
            'description' => ($data['description'] ?? null) ?: null,
        ]);

        $this->activity->log(
            $by,
            'created',
            'supplier',
            "Added supplier {$supplier->name}",
            ['supplier_id' => $supplier->id]
        );

        return $supplier;
    }
}
