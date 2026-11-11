<?php

namespace App\Actions\Collections\AdvanceDeposits;

use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteAdvanceDeposits
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = AdvanceDeposit::query()
            ->whereIn('id', $ids)
            ->whereDoesntHave('payments')
            ->get();

        $deleted = DB::transaction(function () use ($removable) {
            return AdvanceDeposit::whereIn('id', $removable->pluck('id')->all())->delete();
        });

        foreach ($removable as $deposit) {
            $this->activity->log(
                $by,
                'deleted',
                'advance_deposit',
                'Deleted the advance deposit of ' . $deposit->amount_display . ' for ' . $deposit->holder_name,
                ['advance_deposit_id' => $deposit->id, 'user_id' => $deposit->user_id]
            );
        }

        return $deleted;
    }
}
