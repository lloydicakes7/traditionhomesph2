<?php

namespace App\Actions\Collections\ProductCategories;

use App\Actions\Collections\ProductCategories\Concerns\SyncsCategoryAccount;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateProductCategory
{
    use SyncsCategoryAccount;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(ProductCategory $category, array $data, Admin $by): ProductCategory
    {
        $category = DB::transaction(function () use ($category, $data, $by) {
            $category->update($this->payload($data));

            $this->syncIncomeAccount($category, $this->activity, $by->id);

            return $category;
        });

        $this->activity->log(
            $by,
            'updated',
            'product_category',
            "Updated category \"{$category->name}\"",
            ['product_category_id' => $category->id]
        );

        return $category;
    }
}
