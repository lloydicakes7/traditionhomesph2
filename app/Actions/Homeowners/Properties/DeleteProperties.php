<?php

namespace App\Actions\Homeowners\Properties;

use App\Models\Admin;
use App\Models\Property;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteProperties
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle($ids, Admin $by): int
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $count = count($ids);

        DB::transaction(function () use ($ids) {
            Property::whereIn('id', $ids)->delete();
        });

        $this->activity->log(
            $by,
            'deleted',
            'property',
            $count === 1
                ? "Deleted property ID {$ids[0]}"
                : "Deleted {$count} properties"
        );

        return $count;
    }
}
