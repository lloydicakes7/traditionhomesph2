<?php

namespace App\Actions\Homeowners\Addresses;

use App\Models\Address;
use App\Models\Admin;
use App\Services\ActivityLogService;

class DeleteAddress
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): void
    {
        $address = Address::findOrFail($id);

        $this->activity->log(
            $by,
            'deleted',
            'address',
            "Deleted address {$address->label}",
            ['address_id' => $address->id]
        );

        $address->delete();
    }
}
