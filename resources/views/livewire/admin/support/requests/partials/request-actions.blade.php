{{-- One request's actions, as the requests table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Collections tables lay
     theirs out. Which of them shows is decided by where the request stands,
     never by the reader:

       • View          — always: the whole record, whatever its outcome.
       • Create Invoice— always: the office may bill for what it is asked. It
                         needs the homeowner's own account, so the page decides
                         it (see RequestTableList::createInvoice).
       • Print         — approved only: the document is issued once the request
                         has been accepted, which is the point of approving it.
       • Edit          — pending only: a request already decided is not rewritten.
       • Approve       — pending only: accept it and unlock the slip and document.
       • Complete      — approved only: the document has been handed over.
       • Cancel        — pending only: refuse it, and say why.
       • Delete        — always: for one filed in error.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="requestActions{{ $actionKey }}{{ $request->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="requestActions{{ $actionKey }}{{ $request->id }}">
        {{-- VIEW — the whole record, read-only --}}
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#requestViewModal" data-id="{{ $request->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View
        </a>

        {{-- CREATE INVOICE — raise a bill for this homeowner, on the same form
             the Invoices page uses. A request that already names a bill is not
             billed twice: the row says which invoice it carries instead. --}}
        @can('createInvoice', \App\Models\Bill\Bill::class)
            @if ($request->has_invoice)
                <span class="dropdown-item text-muted disabled">
                    <i class="fas fa-fw fa-file-invoice-dollar mr-1"></i>
                    Invoiced{{ $request->bill ? ' · ' . $request->bill->reference : '' }}
                </span>
            @else
                <a class="dropdown-item" href="#" wire:click.prevent="createInvoice({{ $request->id }})">
                    <i class="fas fa-fw fa-file-invoice-dollar mr-1"></i> Create Invoice
                </a>
            @endif
        @endcan

        {{-- PAYMENTS — the invoice this request carries and the receipts filed
             against it. Shown once the office has raised an invoice to track:
             the dialog says whether the homeowner has paid and records a
             waiting portal payment without leaving the Requests page. --}}
        @if ($request->has_invoice)
            @can('viewAny', \App\Models\Payment::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#requestPaymentsModal" data-id="{{ $request->id }}">
                    <i class="fas fa-fw fa-money-check-alt mr-1"></i> Payments
                </a>
            @endcan
        @endif

        {{-- PRINT — the document this request asked for, opened on the Print
             Documents page with the request's own details filled in. --}}
        @if ($request->is_approved && $request->printDocument())
            @can('print.viewAny')
                <a class="dropdown-item"
                    href="{{ route('print.show', ['document' => $request->printDocument()->slug(), 'request' => $request->id]) }}">
                    <i class="fas fa-fw fa-print mr-1"></i> Print
                </a>
            @endcan
        @endif

        {{-- WHAT THE OFFICE DECIDES, while the request is still pending --}}
        @if ($request->is_open)
            @can('update', \App\Models\Request::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#requestEditModal" data-id="{{ $request->id }}">
                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                </a>
            @endcan

            @can('approve', \App\Models\Request::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#requestApproveModal" data-id="{{ $request->id }}">
                    <i class="fas fa-fw fa-check-circle mr-1"></i> Approve
                </a>
            @endcan

            @can('cancel', \App\Models\Request::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#requestCancelModal" data-id="{{ $request->id }}">
                    <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel
                </a>
            @endcan
        @endif

        {{-- COMPLETE — the document has been released and the request is done --}}
        @if ($request->is_approved)
            @can('complete', \App\Models\Request::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#requestCompleteModal" data-id="{{ $request->id }}">
                    <i class="fas fa-fw fa-flag-checkered mr-1"></i> Complete
                </a>
            @endcan
        @endif

        {{-- DELETE --}}
        @can('delete', \App\Models\Request::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#requestDeleteModal" data-id="{{ $request->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
