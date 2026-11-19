<?php

namespace App\Queries\Collections\MonthlyDues;

use App\Enums\BillStatus;
use App\Enums\MonthlyDueGenerationStatus;
use App\Models\Bill\Bill;
use App\Models\Property;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class MonthlyDueGenerationQuery
{
    private CollectionSettingsQuery $settings;

    public function __construct(CollectionSettingsQuery $settings)
    {
        $this->settings = $settings;
    }

    // ===== Rows =====

    public function preview(?string $month = null, int $page = 1, int $perPage = 15): array
    {
        $month = $month ?: PeriodHelper::current();

        $properties = $this->properties();

        $generated = $this->billedBills($month);

        $rows = [];
        $total = '0.00';
        $generatedCount = 0;

        foreach ($properties as $property) {
            $amount = $this->settings->dueFor($property);
            $total = bcadd($total, (string) $amount, 2);

            $bill = $property->owner ? ($generated[(int) $property->owner->id] ?? null) : null;

            if ($bill) {
                $generatedCount++;
            }

            $rows[] = [
                'property_id'    => $property->id,
                'block'          => $property->block ?? 'Not recorded',
                'lot'            => $property->lot ?? 'Not recorded',
                'location'       => $property->address?->street ?: ($property->address?->subdivision ?: 'Not recorded'),
                'owner'          => $property->owner?->name ?: 'Not recorded',
                'type'           => $property->type_enum ? $property->type_enum->label() : '—',
                'sqm'            => $property->sqm !== null ? number_format((float) $property->sqm, 2) : '—',
                'rate'           => MoneyHelper::format($this->settings->rate()),
                'amount'         => $amount,
                'amount_display' => MoneyHelper::format($amount),
                'status'         => $this->statusFor($bill),
            ];
        }

        $propertyCount = count($properties);

        return [
            'month'           => $month,
            'month_display'   => PeriodHelper::label($month),
            'month_options'   => $this->months(),
            'rows'            => $this->paginateRows($rows, $page, $perPage),
            'total'           => $total,
            'total_display'   => MoneyHelper::format($total),
            'rate_cards'      => $this->rateCards(),
            'method'          => $this->settings->billingMethod(),
            'mode'            => $this->settings->generationMode(),
            'generated_count' => $generatedCount,
            'pending_count'   => max(0, $propertyCount - $generatedCount),
            'property_count'  => $propertyCount,
            'penalty'         => $this->settings->penaltyType()->display((float) $this->settings->get('penalty_value', 0)),
            'grace_days'      => (int) $this->settings->get('grace_period_days', 0),
        ];
    }

    public function properties(): Collection
    {
        return Property::query()
            ->withHousehold()
            ->with(['address', 'currentHousehold.user.resident'])
            ->orderBy('block')
            ->orderBy('lot')
            ->get();
    }

    public function billedBills(string $month): array
    {
        return Bill::query()
            ->monthlyDues()
            ->where('billing_period', $month)
            ->get(['id', 'user_id', 'status'])
            ->keyBy('user_id')
            ->all();
    }

    // ===== Options =====

    public function months(int $back = 12): array
    {
        return app(MonthlyDueListQuery::class)->months($back);
    }

    // ===== Helpers =====

    private function statusFor(?Bill $bill): MonthlyDueGenerationStatus
    {
        if (! $bill) {
            return MonthlyDueGenerationStatus::TO_BILL();
        }

        return $bill->status_enum && $bill->status_enum->is(BillStatus::PAID())
            ? MonthlyDueGenerationStatus::PAID()
            : MonthlyDueGenerationStatus::BILLED();
    }

    private function rateCards(): array
    {
        $perSqm = $this->settings->billingMethod()->isPerSquareMeter();

        return [[
            'label' => $perSqm ? 'Rate per SQM' : 'Monthly Due Rate',
            'rate'  => MoneyHelper::formatShort($this->settings->rate()) . ($perSqm ? '/sqm' : ''),
        ]];
    }

    private function paginateRows(array $rows, int $page, int $perPage): LengthAwarePaginator
    {
        $page = max(1, $page);

        return new Paginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page
        );
    }
}
