<?php

namespace App\Actions\Collections\Products;

use App\Models\Admin;
use App\Models\Product\Product;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class ToggleProductActive
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Product $product, Admin $by): bool
    {
        $active = DB::transaction(function () use ($product) {
            $product->update(['is_active' => ! $product->is_active]);

            return (bool) $product->is_active;
        });

        $this->activity->log(
            $by,
            'toggled',
            'product',
            $active
                ? "Made \"{$product->name}\" available to bill"
                : "Stopped billing for \"{$product->name}\"",
            ['product_id' => $product->id]
        );

        return $active;
    }
}
