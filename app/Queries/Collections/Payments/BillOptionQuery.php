<?php

namespace App\Queries\Collections\Payments;

use App\Models\Bill\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class BillOptionQuery
{
    // ===== Options =====

    public function options(?int $ownerId = null): array
    {
        $awaiting = Payment::query()
            ->pending()
            ->whereNotNull('bill_id')
            ->pluck('bill_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        $options = [];

        foreach ($this->open($ownerId) as $bill) {
            if (in_array((int) $bill->id, $awaiting, true)) {
                continue;
            }

            $options[(string) $bill->id] = $bill->reference
                . ' · ' . $bill->type_label
                . ($bill->billing_period ? ' · ' . $bill->billing_period_display : '')
                . ' · Balance ' . $bill->balance_display;
        }

        return $options;
    }

    public function open(?int $ownerId = null): Collection
    {
        return Bill::query()
            ->open()
            ->with('user.household.property')
            ->when($ownerId, function ($query) use ($ownerId) {
                $query->where('user_id', $ownerId);
            })
            ->orderBy('due_date')
            ->orderBy('bill_date')
            ->get();
    }

    public function find(string $value): Bill
    {
        $bill = Bill::with('user.household.property')->find((int) $value);

        if (! $bill) {
            abort(404, 'That bill no longer exists.');
        }

        return $bill;
    }

    public function pending(Bill $bill): ?Payment
    {
        return Payment::query()
            ->pending()
            ->where('bill_id', $bill->id)
            ->ordered()
            ->first();
    }
}
