<?php

namespace App\Queries\Collections\MonthlyDues;

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

        $generated = $this->billedUserIds($month);

        $rows = [];
        $total = 0.0;
        $generatedCount = 0;

        foreach ($properties as $property) {
            $amount = $this->settings->dueFor($property);
            $total += $amount;

            $userId = $property->currentHousehold?->user_id;
            $isGenerated = $userId !== null && isset($generated[(int) $userId]);

            if ($isGenerated) {
                $generatedCount++;
            }

            $rows[] = [
                'property_id'    => $property->id,
                'block'          => $property->block ?? '—',
                'lot'            => $property->lot ?? '—',
                'location'       => $property->address?->street ?: ($property->address?->subdivision ?: '—'),
                'owner'          => $property->owner?->name ?: '—',
                'type'           => $property->type_enum ? $property->type_enum->label() : '—',
                'sqm'            => $property->sqm !== null ? number_format((float) $property->sqm, 2) : '—',
                'rate'           => MoneyHelper::format($this->settings->rate()),
                'amount'         => $amount,
                'amount_display' => MoneyHelper::format($amount),
                'generated'      => $isGenerated,
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

    public function billedUserIds(string $month): array
    {
        $ids = [];

        foreach (Bill::query()->monthlyDues()->where('billing_period', $month)->pluck('user_id') as $id) {
            $ids[(int) $id] = true;
        }

        return $ids;
    }

    // ===== Options =====

    public function months(int $back = 12): array
    {
        return app(MonthlyDueListQuery::class)->months($back);
    }

    // ===== Helpers =====

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
