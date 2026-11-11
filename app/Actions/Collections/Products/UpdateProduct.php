<?php

namespace App\Actions\Collections\Products;

use App\Actions\Collections\Products\Concerns\ProductPayload;
use App\Models\Admin;
use App\Models\Product\Product;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateProduct
{
    use ProductPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Product $product, array $data, Admin $by): Product
    {
        $product = DB::transaction(function () use ($product, $data) {
            $product->update($this->payload($data));

            return $product->fresh('category');
        });

        $this->activity->log(
            $by,
            'updated',
            'product',
            "Updated {$product->type} \"{$product->name}\"",
            ['product_id' => $product->id]
        );

        return $product;
    }
}
