<?php

namespace App\Http\Livewire\Admin\Homeowners\Household;

use App\Http\Livewire\AdminComponent;
use App\Queries\Homeowners\Households\HouseholdStatsQuery;

/**
 * Households page — the stats surface: the 4 small-box stat cards.
 *
 * Isolated from the table so search/sort keystrokes never recompute the
 * stats; it refreshes when a modal emits `household-saved`, keeping the
 * cards current in runtime.
 */
class HouseholdStats extends AdminComponent
{
    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'household-saved' => '$refresh',
    ];

    /** @var HouseholdStatsQuery */
    protected $query;

    public function boot(HouseholdStatsQuery $query)
    {
        $this->query = $query;
    }

    public function render()
    {
        return view('livewire.admin.homeowners.household.household-stats', [
            'stats' => $this->query->summary(),
        ]);
    }
}
