<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Account\AccountingPeriod;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Property;
use App\Services\ActivityLogService;
use App\Services\Collections\MonthlyDueBillingService;
use App\Utility\PeriodHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePastMonthlyDues
{
    private MonthlyDueBillingService $billing;

    private ActivityLogService $activity;

    public function __construct(MonthlyDueBillingService $billing, ActivityLogService $activity)
    {
        $this->billing = $billing;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): array
    {
        $months = PeriodHelper::between($data['from'], $data['to']);
        $scope = (string) ($data['scope'] ?? 'all');
        $userId = (int) ($data['user_id'] ?? 0);

        $result = DB::transaction(function () use ($months, $scope, $userId, $by) {
            $this->guardClosedPeriods($months);

            $properties = Property::query()
                ->withHousehold()
                ->with('currentHousehold')
                ->orderBy('id')
                ->get();

            $created = 0;
            $skipped = 0;

            foreach ($months as $month) {
                $billed = $this->billedUserIds($month);
                $method = $this->billing->billingMethod();
                $dueDate = $this->billing->dueDateFor($month);

                foreach ($properties as $property) {
                    $owner = $property->owner;

                    if (! $owner || ($scope === 'single' && (int) $owner->id !== $userId)) {
                        continue;
                    }

                    if (isset($billed[(int) $owner->id])) {
                        $skipped++;

                        continue;
                    }

                    $this->billing->raise($property, $owner, $month, $dueDate, $method, $by->id);

                    $created++;
                }
            }

            return ['created' => $created, 'skipped' => $skipped];
        });

        if ($result['created'] > 0) {
            $this->activity->log(
                $by,
                'generated',
                'monthly_due',
                'Created ' . $result['created'] . ' past monthly due invoice(s) from '
                    . PeriodHelper::label($data['from']) . ' to ' . PeriodHelper::label($data['to'])
                    . ($result['skipped'] ? ' (' . $result['skipped'] . ' already billed)' : ''),
                ['from' => $data['from'], 'to' => $data['to'], 'created' => $result['created'], 'skipped' => $result['skipped']]
            );
        }

        return $result;
    }

    // ===== Helpers =====

    private function guardClosedPeriods(array $months): void
    {
        foreach ($months as $month) {
            if (AccountingPeriod::locksPeriod($month)) {
                throw ValidationException::withMessages([
                    'from' => PeriodHelper::label($month) . ' is closed — reopen the period before billing it.',
                ]);
            }
        }
    }

    private function billedUserIds(string $month): array
    {
        $ids = [];

        foreach (Bill::query()->monthlyDues()->where('billing_period', $month)->pluck('user_id') as $id) {
            $ids[(int) $id] = true;
        }

        return $ids;
    }
}
