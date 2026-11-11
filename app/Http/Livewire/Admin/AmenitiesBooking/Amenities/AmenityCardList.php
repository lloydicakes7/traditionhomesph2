<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Amenities;

use App\Http\Livewire\AdminComponent;
use App\Models\Amenity\Amenity;
use App\Queries\Amenities\AmenityListQuery;
use Livewire\WithPagination;

class AmenityCardList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search'];

    public $search = '';

    protected $listeners = [
        'amenity-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AmenityListQuery */
    protected $amenities;

    public function boot(AmenityListQuery $amenities)
    {
        $this->amenities = $amenities;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Amenity::class);

        return view('livewire.admin.amenities-booking.amenities.amenity-card-list', [
            'amenities' => $this->amenities->paginate($this->search),
        ]);
    }
}
