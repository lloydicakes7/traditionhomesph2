<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner;

use App\Http\Livewire\AdminComponent;
use App\Queries\Homeowners\Homeowners\HomeownerStatsQuery;

class HomeownerStats extends AdminComponent
{
    protected $listeners = [
        'homeowner-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HomeownerStatsQuery */
    protected $query;

    public function boot(HomeownerStatsQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.homeowner-stats', [
            'stats' => $this->query->summary(),
        ]);
    }
}
