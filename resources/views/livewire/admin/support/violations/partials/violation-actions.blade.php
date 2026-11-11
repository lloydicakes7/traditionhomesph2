{{-- One violation's actions, as the violations table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Requests page lays
     theirs out. Which of them shows is decided by the record, never by the
     reader:

       • View    — always: the whole record, read-only.
       • Print   — always: the notice the office hands the homeowner, built by
                   the same document pipeline the Print Documents page uses.
       • Edit    — while the office may still correct it.
       • Resolve — once the violation can be closed out.
       • Delete  — for one issued in error.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="violationActions{{ $actionKey }}{{ $violation->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right"
        aria-labelledby="violationActions{{ $actionKey }}{{ $violation->id }}">
        {{-- VIEW — the whole record, read-only --}}
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#violationViewModal" data-id="{{ $violation->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Notice
        </a>

        {{-- PRINT — the Notice of Violation this record issues --}}
        <a class="dropdown-item" href="{{ route('support.violations.notice', $violation->id) }}">
            <i class="fas fa-fw fa-print mr-1"></i> Print Notice
        </a>

        {{-- EDIT — correct the record while it is still open --}}
        @can('update', \App\Models\Violation::class)
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#violationFormModal" data-id="{{ $violation->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit
            </a>
        @endcan

        {{-- RESOLVE — close it out once it can be --}}
        @if ($violation->can_be_resolved)
            @can('resolve', \App\Models\Violation::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#violationResolveModal" data-id="{{ $violation->id }}">
                    <i class="fas fa-fw fa-check mr-1"></i> Resolve Violation
                </a>
            @endcan
        @endif

        {{-- DELETE --}}
        @can('delete', \App\Models\Violation::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#violationDeleteModal" data-id="{{ $violation->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
