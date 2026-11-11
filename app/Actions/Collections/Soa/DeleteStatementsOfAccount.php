<?php

namespace App\Actions\Collections\Soa;

use App\Models\Admin;
use App\Models\SoaEmail;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteStatementsOfAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $deleted = DB::transaction(function () use ($ids) {
            return SoaEmail::whereIn('id', $ids)->delete();
        });

        $this->activity->log(
            $by,
            'deleted',
            'soa_email',
            'Removed ' . $deleted . ' statement(s) of account',
            ['statements' => $deleted]
        );

        return $deleted;
    }
}
