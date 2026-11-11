<?php

namespace App\Actions\Homeowners\Addresses;

use App\Models\Address;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class CreateAddress
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Address
    {
        $address = DB::transaction(function () use ($data) {
            if (! empty($data['is_primary'])) {
                Address::where('is_primary', true)->update(['is_primary' => false]);
            }

            return Address::create($data);
        });

        $this->activity->log(
            $by,
            'created',
            'address',
            "Created address {$address->label}",
            ['address_id' => $address->id]
        );

        return $address;
    }
}
