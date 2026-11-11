<?php

namespace App\Services\Collections;

use App\Enums\AdjustmentType;
use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Enums\BillingMethod;
use App\Models\Bill\Bill;
use App\Models\Property;
use App\Models\User;
use App\Utility\PeriodHelper;
use App\Utility\SettingsHelper;
use Illuminate\Support\Carbon;

class MonthlyDueBillingService
{
    // ===== Operations =====

    public function raise(
        Property $property,
        User $user,
        string $month,
        Carbon $dueDate,
        BillingMethod $method,
        ?int $adminId
    ): Bill {
        $amount = $this->dueFor($property);
        $rate = $this->rate();

        $due = Bill::create([
            'reference'           => Bill::nextReference(BillType::MONTHLY_DUE(), $month),
            'user_id'             => $user->id,
            'billed_to_name'      => $user->name,
            'type'                => BillType::MONTHLY_DUE,
            'billing_period'      => $month,
            'bill_date'           => now()->toDateString(),
            'due_date'            => $dueDate,
            'status'              => BillStatus::UNPAID,
            'created_by_admin_id' => $adminId,
        ]);

        $due->items()->create([
            'name'       => 'Monthly Dues ' . PeriodHelper::label($month),
            'quantity'   => $method->isPerSquareMeter() ? ($property->sqm ?: 0) : 1,
            'unit_price' => $rate,
            'amount'     => $amount,
        ]);

        $due->recalculate();

        return $due;
    }

    // ===== Settings =====

    public function billingMethod(): BillingMethod
    {
        return BillingMethod::tryFrom($this->setting('billing_method', BillingMethod::PER_SQM))
            ?: BillingMethod::PER_SQM();
    }

    public function rate(): float
    {
        return (float) $this->setting('monthly_rate', '0');
    }

    public function dueFor(Property $property): float
    {
        $rate = $this->rate();

        if ($this->billingMethod()->isPerSquareMeter()) {
            return round($rate * (float) $property->sqm, 2);
        }

        return round($rate, 2);
    }

    public function penaltyFor(float $amount): float
    {
        $type = AdjustmentType::tryFrom($this->setting('penalty_type', AdjustmentType::FIXED))
            ?: AdjustmentType::FIXED();

        return $type->compute($amount, (float) $this->setting('penalty_value', '0'));
    }

    public function dueDateFor(string $month): Carbon
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $day = min(max(1, (int) $this->setting('generation_day', '1')), $start->daysInMonth);
        $grace = (int) $this->setting('grace_period_days', '0');

        $scheduled = $start->copy()->day($day)->addDays($grace);
        $fromToday = Carbon::now()->startOfDay()->addDays($grace);

        return $scheduled->greaterThan($fromToday) ? $scheduled : $fromToday;
    }

    // ===== Helpers =====

    private function setting(string $key, string $default): string
    {
        return (string) SettingsHelper::get($key, 'collections', $default);
    }
}
