<?php

namespace App\Actions\Expenses\Suppliers;

use App\Models\Admin;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteSuppliers
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $suppliers = Supplier::query()->whereIn('id', $ids)->withCount('expenses')->get();

        $used = $suppliers->filter(function (Supplier $supplier) {
            return $supplier->expenses_count > 0;
        });

        if ($used->isNotEmpty()) {
            throw ValidationException::withMessages([
                'delete' => 'Cannot delete ' . $used->pluck('name')->implode(', ')
                    . ' — expenses already name it.',
            ]);
        }

        $deleted = DB::transaction(function () use ($suppliers) {
            foreach ($suppliers as $supplier) {
                $supplier->delete();
            }

            return $suppliers->count();
        });

        foreach ($suppliers as $supplier) {
            $this->activity->log(
                $by,
                'deleted',
                'supplier',
                "Deleted supplier {$supplier->name} — its records keep the name they were filed under",
                ['supplier_id' => $supplier->id]
            );
        }

        return $deleted;
    }
}
