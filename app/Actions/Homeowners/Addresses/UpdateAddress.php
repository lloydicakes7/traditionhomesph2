<?php

namespace App\Actions\Homeowners\Addresses;

use App\Models\Address;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class UpdateAddress
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, array $data, Admin $by): Address
    {
        $address = DB::transaction(function () use ($id, $data) {
            if (! empty($data['is_primary'])) {
                Address::where('is_primary', true)->update(['is_primary' => false]);
            }

            $address = Address::findOrFail($id);
            $address->update($data);

            return $address->fresh();
        });

        $this->activity->log(
            $by,
            'updated',
            'address',
            "Updated address {$address->label}",
            ['address_id' => $address->id]
        );

        return $address;
    }
}
