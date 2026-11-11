{{-- One property's actions, as the properties table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Collections and Support
     tables lay theirs out. Assigning and removing the occupant stays in the
     Homeowner column, next to the person it is about — this is the unit's own
     record:

       • View Details — the unit and whoever holds it, read-only.
       • Edit         — the unit's own facts (block, lot, type, area, photo).
       • Delete       — for a unit entered in error.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="propertyActions{{ $actionKey }}{{ $property->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="propertyActions{{ $actionKey }}{{ $property->id }}">
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#propertyViewModal" data-id="{{ $property->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Details
        </a>

        @can('update', \App\Models\Property::class)
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#propertyFormModal" data-id="{{ $property->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit
            </a>
        @endcan

        @can('delete', \App\Models\Property::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#propertyDeleteModal" data-id="{{ $property->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
