<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident;

use App\Http\Livewire\AdminComponent;
use App\Queries\Homeowners\Residents\ResidentStatsQuery;

/**
 * Residents page — the stats surface: the 4 small-box stat cards.
 *
 * Isolated from the table so search/sort keystrokes never recompute the
 * stats; it refreshes when a modal emits `resident-saved`, keeping the
 * cards current in runtime.
 */
class ResidentStats extends AdminComponent
{
    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'resident-saved' => '$refresh',
    ];

    /** @var ResidentStatsQuery */
    protected $query;

    public function boot(ResidentStatsQuery $query)
    {
        $this->query = $query;
    }

    public function render()
    {
        return view('livewire.admin.homeowners.resident.resident-stats', [
            'stats' => $this->query->summary(),
        ]);
    }
}
