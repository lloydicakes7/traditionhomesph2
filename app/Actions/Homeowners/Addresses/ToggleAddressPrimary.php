<?php

namespace App\Actions\Homeowners\Addresses;

use App\Models\Address;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class ToggleAddressPrimary
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(int $id, Admin $by): bool
    {
        $address = Address::findOrFail($id);
        $wasPrimary = $address->is_primary;

        DB::transaction(function () use ($address, $wasPrimary) {
            if ($wasPrimary) {
                $address->update(['is_primary' => false]);

                return;
            }

            Address::where('is_primary', true)->update(['is_primary' => false]);
            $address->update(['is_primary' => true]);
        });

        $this->activity->log(
            $by,
            'updated',
            'address',
            ($wasPrimary ? 'Removed primary status from' : 'Set as primary') . " address {$address->label}",
            ['address_id' => $address->id, 'is_primary' => ! $wasPrimary]
        );

        return ! $wasPrimary;
    }
}
