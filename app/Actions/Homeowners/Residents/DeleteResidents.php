<?php

namespace App\Actions\Homeowners\Residents;

use App\Models\Admin;
use App\Models\Resident;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteResidents
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle($ids, Admin $by): int
    {
        $ids = (array) $ids;

        DB::transaction(function () use ($ids) {
            User::whereIn('resident_id', $ids)->update(['resident_id' => null]);
            Resident::whereIn('id', $ids)->delete();
        });

        $count = count($ids);

        $this->activity->log(
            $by,
            'deleted',
            'homeowner',
            $count > 1
                ? "Deleted {$count} residents"
                : 'Deleted resident #' . $ids[0]
        );

        return $count;
    }
}
