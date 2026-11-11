{{-- One household's actions, as the households table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Collections and Support
     tables lay theirs out:

       • View Details    — the household and its members, read-only.
       • Edit Household  — the unit, the tenure, the RBI composition and income.
       • Edit Members    — who lives in the unit and how they are related.
       • Delete          — for a household entered in error.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="householdActions{{ $actionKey }}{{ $household->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="householdActions{{ $actionKey }}{{ $household->id }}">
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#householdViewModal" data-id="{{ $household->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Details
        </a>

        @can('update', \App\Models\Household\Household::class)
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#householdFormModal" data-id="{{ $household->id }}">
                <i class="fas fa-fw fa-home mr-1"></i> Edit Household
            </a>
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#editMembersModal" data-id="{{ $household->id }}">
                <i class="fas fa-fw fa-users mr-1"></i> Edit Members
            </a>
        @endcan

        @can('printRbi', \App\Models\Household\Household::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('rbi.form-a.download', $household) }}">
                <i class="fas fa-fw fa-file-pdf mr-1"></i> Print RBI Form A
            </a>
        @endcan

        @can('delete', \App\Models\Household\Household::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#householdDeleteModal" data-id="{{ $household->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
