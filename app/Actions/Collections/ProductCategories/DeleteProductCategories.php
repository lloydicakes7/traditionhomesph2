<?php

namespace App\Actions\Collections\ProductCategories;

use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteProductCategories
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = ProductCategory::whereIn('id', $ids)->get();

        $deleted = DB::transaction(function () use ($removable) {
            return ProductCategory::whereIn('id', $removable->pluck('id')->all())->delete();
        });

        foreach ($removable as $category) {
            $this->activity->log(
                $by,
                'deleted',
                'product_category',
                "Deleted category \"{$category->name}\"",
                ['product_category_id' => $category->id]
            );
        }

        return $deleted;
    }
}
