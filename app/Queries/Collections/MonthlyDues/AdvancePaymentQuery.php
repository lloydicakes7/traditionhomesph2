<?php

namespace App\Queries\Collections\MonthlyDues;

use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\User;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;

class AdvancePaymentQuery
{
    private CollectionSettingsQuery $settings;

    private PastMonthlyDueQuery $pastDues;

    public function __construct(CollectionSettingsQuery $settings, PastMonthlyDueQuery $pastDues)
    {
        $this->settings = $settings;
        $this->pastDues = $pastDues;
    }

    // ===== Queries =====

    public function preview(int $userId, ?string $from, ?string $to): array
    {
        $empty = $this->emptyPreview();

        if (! $userId || ! $this->isMonth($from) || ! $this->isMonth($to) || $from > $to) {
            return $empty;
        }

        $user = User::with('household.property')->find($userId);
        $property = $user?->household?->property;

        if (! $property) {
            return $empty;
        }

        $months = PeriodHelper::between($from, $to);
        $bills = Bill::query()
            ->monthlyDues()
            ->forUser($userId)
            ->whereIn('billing_period', $months)
            ->get()
            ->keyBy('billing_period');

        $waiting = Payment::query()
            ->pending()
            ->whereIn('bill_id', $bills->pluck('id')->all())
            ->pluck('bill_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $newAmount = $this->settings->dueFor($property);
        $preview = $empty;
        $total = '0.00';

        foreach ($months as $month) {
            $row = $this->classifyMonth($bills->get($month), $waiting, $newAmount);

            $preview[$row['bucket']]++;
            $total = bcadd($total, $row['amount'], 2);
        }

        $preview['total'] = $total;
        $preview['total_display'] = MoneyHelper::format($total);

        return $preview;
    }

    public function ownerOptions(): array
    {
        return $this->pastDues->ownerOptions();
    }

    public function depositOptions(int $userId): array
    {
        return AdvanceDeposit::query()
            ->forUser($userId)
            ->ordered()
            ->get()
            ->mapWithKeys(function (AdvanceDeposit $deposit) {
                $credit = $deposit->is_open
                    ? $deposit->balance_display . ' available'
                    : 'No credit left';

                return [$deposit->id => $credit . ' · deposited ' . $deposit->deposited_at_display];
            })
            ->all();
    }

    // ===== Helpers =====

    private function classifyMonth(?Bill $bill, array $waiting, float $newAmount): array
    {
        if (! $bill) {
            return ['bucket' => 'created', 'amount' => (string) $newAmount];
        }

        if (in_array((int) $bill->id, $waiting, true)) {
            return ['bucket' => 'skipped', 'amount' => '0.00'];
        }

        $collectible = $bill->status_enum && $bill->status_enum->isCollectible();
        $balance = (string) $bill->balance;

        if (! $collectible || bccomp($balance, '0.01', 2) < 0) {
            return ['bucket' => 'skipped', 'amount' => '0.00'];
        }

        return ['bucket' => 'settled', 'amount' => $balance];
    }

    private function emptyPreview(): array
    {
        return [
            'created'       => 0,
            'settled'       => 0,
            'skipped'       => 0,
            'total'         => '0.00',
            'total_display' => MoneyHelper::format(0),
        ];
    }

    private function isMonth($value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) === 1;
    }
}
