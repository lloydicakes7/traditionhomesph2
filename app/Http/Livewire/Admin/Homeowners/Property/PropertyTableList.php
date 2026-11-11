<?php

namespace App\Http\Livewire\Admin\Homeowners\Property;

use App\Actions\Homeowners\Households\DeleteHouseholds;
use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithSorting;
use App\Models\Property;
use App\Enums\PropertyType;
use App\Queries\Homeowners\Households\HouseholdListQuery;
use App\Queries\Homeowners\Properties\PropertyListQuery;
use Livewire\WithPagination;

class PropertyTableList extends AdminComponent
{
    use WithPagination,
        WithSorting;

    // =====================================================================
    // List state
    // =====================================================================

    public $search = '';

    public $filterType = '';

    public $filterOccupancy = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $selectedIds = [];

    public $selectAll = false;

    protected $queryString = ['search', 'filterType', 'filterOccupancy', 'sortField', 'sortDirection'];

    protected $listeners = [
        'property-saved' => 'refreshList',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PropertyListQuery */
    protected $query;

    /** @var HouseholdListQuery */
    protected $households;

    /** @var DeleteHouseholds */
    protected $deleteHouseholds;

    public function boot(
        PropertyListQuery $query,
        HouseholdListQuery $households,
        DeleteHouseholds $deleteHouseholds
    ) {
        $this->query = $query;
        $this->households = $households;
        $this->deleteHouseholds = $deleteHouseholds;
    }

    // =====================================================================
    // WithSorting
    // =====================================================================

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterOccupancy(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids(
            $this->search, $this->filterType, $this->filterOccupancy
        );
    }

    /**
     * Toggle "all pages" using the service-filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    // =====================================================================
    // Row actions needing the server
    // =====================================================================

    /**
     * Another component wrote a property — including the mass-delete dialog.
     * Drop the selection it consumed, so a bulk delete never leaves the toolbar
     * standing over rows that are already gone.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * Remove the homeowner from a property.
     */
    public function removeHomeowner($propertyId): void
    {
        $this->authorize('update', Property::class);

        $householdIds = $this->households->idsForProperty((int) $propertyId);

        if ($householdIds !== []) {
            $this->deleteHouseholds->handle($householdIds, auth('web')->user());
        }

        $this->emit('notify', 'success', 'Homeowner removed from property.');
        $this->emit('property-saved');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Property::class);

        $properties = $this->query->paginate(
            $this->search, $this->filterType, $this->filterOccupancy,
            $this->sortField, $this->sortDirection
        );

        return view('livewire.admin.homeowners.property.property-table-list', [
            'properties' => $properties,
            'types'      => PropertyType::labels(),
        ]);
    }
}
