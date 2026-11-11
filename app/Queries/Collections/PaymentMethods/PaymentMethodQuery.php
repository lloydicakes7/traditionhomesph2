<?php

namespace App\Queries\Collections\PaymentMethods;

use App\Models\PaymentMethod;
use App\Utility\CacheHelper;
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
        return CacheHelper::remember('collections.payment_method.options', ['options'], 3600, function () {
            return PaymentMethod::query()->ordered()->pluck('name', 'id')->all();
        });
    }

    public function active(): Collection
    {
        return CacheHelper::remember('collections.payment_method.options', ['active'], 3600, function () {
            return PaymentMethod::query()->active()->ordered()->get();
        });
    }

}
