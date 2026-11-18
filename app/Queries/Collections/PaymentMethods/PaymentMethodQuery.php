<?php

namespace App\Queries\Collections\PaymentMethods;

use App\Models\PaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodQuery
{
    // ===== Rows =====

    public function paginate(array $criteria = []): LengthAwarePaginator
    {
        return PaymentMethod::query()
            ->search((string) ($criteria['search'] ?? ''))
            ->ordered()
            ->paginate((int) ($criteria['perPage'] ?? 15));
    }

    public function find(int $id): PaymentMethod
    {
        return PaymentMethod::findOrFail($id);
    }

    // ===== Options =====

    public function options(): array
    {
        return PaymentMethod::query()->ordered()->pluck('name', 'id')->all();
    }

    /**
     * The methods a payment can be recorded against. Only the on/off switch
     * decides: a method without a QR code is still offered.
     */
    public function active(): Collection
    {
        return PaymentMethod::query()->active()->ordered()->get();
    }
}
