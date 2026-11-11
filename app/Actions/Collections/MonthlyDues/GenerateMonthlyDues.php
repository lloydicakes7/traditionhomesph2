<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Services\Collections\MonthlyDueBillingService;
use App\Utility\PeriodHelper;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyDues
{
    private MonthlyDueBillingService $billing;

    private ActivityLogService $activity;

    public function __construct(MonthlyDueBillingService $billing, ActivityLogService $activity)
    {
        $this->billing = $billing;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $month, ?Admin $by = null): array
    {
        $result = DB::transaction(function () use ($month, $by) {
            return $this->raise($month, $by?->id);
        });

        if ($by) {
            $this->log($month, $result, $by);
        }

        return $result;
    }

    // ===== Helpers =====

    private function raise(string $month, ?int $adminId): array
    {
        $properties = Property::query()
            ->withHousehold()
            ->with('currentHousehold')
            ->orderBy('id')
            ->get();

        $alreadyBilled = $this->billedUserIds($month);

        $method = $this->billing->billingMethod();
        $dueDate = $this->billing->dueDateFor($month);

        $created = 0;
        $skipped = 0;

        foreach ($properties as $property) {
            $user = $property->owner;

            if (! $user || isset($alreadyBilled[(int) $user->id])) {
                $skipped++;

                continue;
            }

            $this->billing->raise($property, $user, $month, $dueDate, $method, $adminId);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    private function billedUserIds(string $month): array
    {
        $ids = [];

        foreach (Bill::query()->monthlyDues()->where('billing_period', $month)->pluck('user_id') as $id) {
            $ids[(int) $id] = true;
        }

        return $ids;
    }

    private function log(string $month, array $result, Admin $by): void
    {
        $description = 'Generated ' . $result['created'] . ' monthly due invoice(s) for ' . PeriodHelper::label($month)
            . ($result['skipped'] ? ' (' . $result['skipped'] . ' already billed)' : '');

        $this->activity->log($by, 'generated', 'monthly_due', $description, [
            'month'   => $month,
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]);
    }
}
