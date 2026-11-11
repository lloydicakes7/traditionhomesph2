<?php

namespace App\Http\Livewire\Admin\Homeowners\Property;

use App\Http\Livewire\AdminComponent;
use App\Queries\Homeowners\Properties\PropertyStatsQuery;

/**
 * Properties page — the stats surface: the 4 small-box stat cards.
 *
 * Isolated from the table so search/sort keystrokes never recompute the
 * stats; it refreshes when a modal emits `property-saved`, keeping the
 * cards current in runtime.
 */
class PropertyStats extends AdminComponent
{
    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'property-saved' => '$refresh',
    ];

    /** @var PropertyStatsQuery */
    protected $query;

    public function boot(PropertyStatsQuery $query)
    {
        $this->query = $query;
    }

    public function render()
    {
        return view('livewire.admin.homeowners.property.property-stats', [
            'stats' => $this->query->summary(),
        ]);
    }
}
