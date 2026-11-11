<?php

namespace App\Actions\Collections\Products;

use App\Models\Admin;
use App\Models\Product\Product;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteProducts
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = Product::whereIn('id', $ids)->get();

        $deleted = DB::transaction(function () use ($removable) {
            return Product::whereIn('id', $removable->pluck('id')->all())->delete();
        });

        foreach ($removable as $product) {
            $this->activity->log(
                $by,
                'deleted',
                'product',
                "Deleted \"{$product->name}\"",
                ['product_id' => $product->id]
            );
        }

        return $deleted;
    }
}
