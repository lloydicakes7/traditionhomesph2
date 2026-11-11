<?php

namespace App\Queries\Homeowners\Addresses;

use App\Models\Address;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AddressListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return Address::query()
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): Address
    {
        return Address::findOrFail($id);
    }
}
