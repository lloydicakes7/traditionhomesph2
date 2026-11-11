<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\CommunityReportQuery;

/**
 * The Community Reports page — the association's community data rolled up:
 * who uses the portal, what the amenities are booking, and what the office
 * keeps on file. It carries no export: it is a snapshot of several modules at
 * once rather than one table a CSV could hold, and each figure it shows is
 * reachable from the module that owns it.
 */
class Community extends AdminComponent
{
    /** @var CommunityReportQuery */
    protected $community;

    public function boot(CommunityReportQuery $community)
    {
        $this->community = $community;
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.community', [
            'report' => $this->community->summary(),
        ]);
    }
}
