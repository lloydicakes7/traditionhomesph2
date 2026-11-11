<?php

namespace App\Queries\Collections\MonthlyDues;

use App\Models\Bill\Bill;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;

class PastMonthlyDueQuery
{
    private MonthlyDueGenerationQuery $generation;

    private CollectionSettingsQuery $settings;

    public function __construct(MonthlyDueGenerationQuery $generation, CollectionSettingsQuery $settings)
    {
        $this->generation = $generation;
        $this->settings = $settings;
    }

    // ===== Queries =====

    public function preview(string $scope, ?int $userId, string $from, string $to): array
    {
        $months = PeriodHelper::between($from, $to);
        $billed = $this->billedUserIds($months);

        $created = 0;
        $skipped = 0;
        $total = 0.0;

        foreach ($this->generation->properties() as $property) {
            $owner = $property->owner;

            if (! $owner || ! $this->inScope($scope, $userId, (int) $owner->id)) {
                continue;
            }

            foreach ($months as $month) {
                if (isset($billed[(int) $owner->id][$month])) {
                    $skipped++;

                    continue;
                }

                $created++;
                $total += $this->settings->dueFor($property);
            }
        }

        return [
            'created'       => $created,
            'skipped'       => $skipped,
            'total'         => $total,
            'total_display' => MoneyHelper::format($total),
        ];
    }

    public function ownerOptions(): array
    {
        $options = [];

        foreach ($this->generation->properties() as $property) {
            $owner = $property->owner;

            if (! $owner) {
                continue;
            }

            $unit = $property->block_lot_label;
            $options[$owner->id] = $owner->name . (in_array($unit, ['', '—', 'None'], true) ? '' : ' · ' . $unit);
        }

        return $options;
    }

    // ===== Helpers =====

    private function billedUserIds(array $months): array
    {
        $billed = [];

        foreach (Bill::query()->monthlyDues()->whereIn('billing_period', $months)->get(['user_id', 'billing_period']) as $bill) {
            $billed[(int) $bill->user_id][$bill->billing_period] = true;
        }

        return $billed;
    }

    private function inScope(string $scope, ?int $userId, int $ownerId): bool
    {
        return $scope !== 'single' || $userId === $ownerId;
    }
}
