<?php

namespace App\Actions\Collections\ProductCategories;

use App\Actions\Collections\ProductCategories\Concerns\SyncsCategoryAccount;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateProductCategory
{
    use SyncsCategoryAccount;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): ProductCategory
    {
        $category = DB::transaction(function () use ($data, $by) {
            $category = ProductCategory::create($this->payload($data));

            $this->syncIncomeAccount($category, $this->activity, $by->id);

            return $category;
        });

        $this->activity->log(
            $by,
            'created',
            'product_category',
            "Added category \"{$category->name}\"",
            ['product_category_id' => $category->id]
        );

        return $category;
    }
}
