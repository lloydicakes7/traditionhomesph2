<?php

namespace App\Http\Livewire\Portal\HouseholdProperty;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;

class HouseholdPropertyDetails extends PortalComponent
{
    use ResolvesHomeownerAccount;

    protected $listeners = [
        'household-members-updated' => '$refresh',
        'household-details-updated' => '$refresh',
    ];

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $household = $this->household();
        $property = $household ? $household->property : null;

        if ($household) {
            $this->authorize('viewOwn', $household);
        }

        if ($property) {
            $this->authorize('viewOwn', $property);
        }

        return view('livewire.portal.household-property.household-property-details', [
            'household' => $household,
            'property'  => $property,
            'occupancy' => $household ? $household->type_enum : null,
            'type'      => $property ? $property->type_enum : null,
        ]);
    }

    // =====================================================================
    // Tools
    // =====================================================================

    private function household()
    {
        return $this->account()->household()
            ->with(['property.address', 'memberRecords.resident.user'])
            ->first();
    }
}
