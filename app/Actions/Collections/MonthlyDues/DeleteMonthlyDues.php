<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteMonthlyDues
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $ids, Admin $by): int
    {
        $removable = Bill::query()->monthlyDues()
            ->whereIn('id', $ids)
            ->whereDoesntHave('payments')
            ->with('user.household.property')
            ->get();

        $deleted = DB::transaction(function () use ($removable) {
            return Bill::whereIn('id', $removable->pluck('id')->all())->delete();
        });

        foreach ($removable as $due) {
            $this->activity->log(
                $by,
                'deleted',
                'monthly_due',
                'Deleted monthly due ' . $due->reference . ' for ' . ($due->property?->block_lot_label ?? '—'),
                ['bill_id' => $due->id]
            );
        }

        return $deleted;
    }
}
