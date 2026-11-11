<?php

namespace App\Queries\Collections\Soa;

use App\Models\Bill\Bill;
use App\Models\Property;
use App\Models\SoaEmail;
use App\Models\User;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Utility\CacheHelper;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;

class StatementDataQuery
{
    private CollectionSettingsQuery $settingsQuery;

    public function __construct(CollectionSettingsQuery $settingsQuery)
    {
        $this->settingsQuery = $settingsQuery;
    }

    // ===== Document data =====

    public function summary(SoaEmail $statement): array
    {
        return $this->build(
            $statement->user?->household?->property,
            $statement->user_id,
            $statement->period,
            $statement->issued_to_name,
            $statement->property_label,
            $statement->period_display
        );
    }

    public function forUser(int $userId, ?string $period = null): array
    {
        $user = User::with(['household.property.address', 'resident'])->findOrFail($userId);
        $property = $user->household?->property;

        return $this->build(
            $property,
            $user->id,
            $period,
            $user->resident?->full_name ?: ($user->email ?: '—'),
            $property ? $property->block_lot_label : '—',
            PeriodHelper::label($period)
        );
    }

    // ===== Options =====

    public function accounts(): array
    {
        return CacheHelper::remember('collections.statement.options', ['accounts'], 3600, function () {
            return User::query()
                ->whereHas('bills', function ($query) {
                    $query->open();
                })
                ->with('resident')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(function (User $user) {
                    $name = $user->resident?->full_name;

                    if ($name && $user->email) {
                        $label = $name . ' · ' . $user->email;
                    } else {
                        $label = $name ?: $user->email ?: ('Account #' . $user->id);
                    }

                    return [$user->id => $label];
                })
                ->all();
        });
    }

    // ===== Helpers =====

    private function build(
        ?Property $property,
        ?int $userId,
        ?string $period,
        string $owner,
        string $propertyLabel,
        string $periodLabel
    ): array {
        $bills = $userId === null ? collect() : Bill::query()
            ->open()
            ->forUser($userId)
            ->with('items')
            ->when($period, function ($query) use ($period) {
                $query->forMonth($period);
            })
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get();

        $balance = (float) $bills->sum('balance');

        return [
            'owner'           => $owner,
            'property'        => $propertyLabel,
            'address'         => $property && $property->address ? $property->address->short_address : '—',
            'period'          => $periodLabel,
            'bills'           => $bills,
            'balance'         => $balance,
            'balance_display' => MoneyHelper::format($balance),
            'instructions'    => $this->settingsQuery->renderInstructions([
                'OWNER_NAME'            => $owner,
                'PROPERTY'              => $propertyLabel,
                'AMOUNT_DUE'            => MoneyHelper::format($balance),
                'HOA_NAME'              => $this->settingsQuery->get('hoa_name', config('app.name')),
            ]),
        ];
    }
}
