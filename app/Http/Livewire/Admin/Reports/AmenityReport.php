<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\AmenityReportQuery;

/**
 * The Amenities Report — what the association's shared facilities are doing:
 * how the bookings stand, which amenity is booked most, what each has earned,
 * and the same figures as one table. It carries no export, like the Community
 * and Residents Reports: it is several readings of one module at once rather
 * than one table a CSV could hold, and every figure in it is reachable from the
 * Amenities Booking module that owns the record.
 */
class AmenityReport extends AdminComponent
{
    /** @var AmenityReportQuery */
    protected $amenities;

    public function boot(AmenityReportQuery $amenities)
    {
        $this->amenities = $amenities;
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.amenities', [
            'report' => $this->amenities->summary(),
        ]);
    }
}
