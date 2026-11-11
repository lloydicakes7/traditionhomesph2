<?php

namespace App\Actions\Collections\Products;

use App\Actions\Collections\Products\Concerns\ProductPayload;
use App\Models\Admin;
use App\Models\Product\Product;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateProduct
{
    use ProductPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Product
    {
        $product = DB::transaction(function () use ($data) {
            return Product::create($this->payload($data));
        });

        $this->activity->log(
            $by,
            'created',
            'product',
            "Added {$product->type} \"{$product->name}\"",
            ['product_id' => $product->id]
        );

        return $product;
    }
}
