{{-- One complaint's actions, as the complaints table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Requests page lays
     theirs out. Which of them shows is decided by the record, never by the
     reader:

       • View    — always: the whole record, read-only.
       • Respond — while the office may still answer it (again).
       • Edit    — only a complaint the office filed itself; a portal filing is
                   the submitter's own record.
       • Delete  — for one filed in error.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="complaintActions{{ $actionKey }}{{ $complaint->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right"
        aria-labelledby="complaintActions{{ $actionKey }}{{ $complaint->id }}">
        {{-- VIEW — the whole record, read-only --}}
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#complaintViewModal" data-id="{{ $complaint->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Details
        </a>

        {{-- RESPOND — answer it, or update the answer --}}
        @if ($complaint->can_be_responded_to)
            @can('respond', \App\Models\Complaint::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#complaintRespondModal" data-id="{{ $complaint->id }}">
                    <i class="fas fa-fw fa-reply mr-1"></i>
                    {{ $complaint->is_responded ? 'Update Response' : 'Respond' }}
                </a>
            @endcan
        @endif

        {{-- EDIT — only a complaint the office itself filed --}}
        @unless ($complaint->submitted_by_homeowner)
            @can('update', \App\Models\Complaint::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#complaintFormModal" data-id="{{ $complaint->id }}">
                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                </a>
            @endcan
        @endunless

        {{-- DELETE --}}
        @can('delete', \App\Models\Complaint::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#complaintDeleteModal" data-id="{{ $complaint->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
