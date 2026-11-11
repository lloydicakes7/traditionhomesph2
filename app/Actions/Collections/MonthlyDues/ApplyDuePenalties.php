<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Services\ActivityLogService;
use App\Services\Collections\MonthlyDueBillingService;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApplyDuePenalties
{
    private MonthlyDueBillingService $billing;

    private ActivityLogService $activity;

    public function __construct(MonthlyDueBillingService $billing, ActivityLogService $activity)
    {
        $this->billing = $billing;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(?Admin $by = null): int
    {
        $penalised = DB::transaction(function () {
            return $this->penalise();
        });

        if ($by) {
            $this->log($penalised, $by);
        }

        return $penalised->count();
    }

    // ===== Helpers =====

    private function penalise(): Collection
    {
        $penalised = new Collection();

        $overdue = Bill::query()->monthlyDues()
            ->open()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', Carbon::today()->toDateString())
            ->where('penalty', '<=', 0)
            ->get();

        foreach ($overdue as $due) {
            $penalty = $this->billing->penaltyFor((float) $due->balance);

            if ($penalty <= 0) {
                continue;
            }

            $due->penalty = $penalty;
            $due->recalculate();

            $penalised->push($due);
        }

        return $penalised;
    }

    private function log(Collection $penalised, Admin $by): void
    {
        foreach ($penalised as $due) {
            $this->activity->log(
                $by,
                'penalised',
                'monthly_due',
                'Penalised overdue monthly due ' . $due->reference . ' by ' . MoneyHelper::format($due->penalty),
                ['bill_id' => $due->id, 'penalty' => (float) $due->penalty]
            );
        }
    }
}
