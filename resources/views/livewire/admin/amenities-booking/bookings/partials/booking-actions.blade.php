<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="{{ $actionPrefix }}Actions{{ $booking->id }}" data-toggle="dropdown"
        data-boundary="viewport" aria-haspopup="true" aria-expanded="false"
        wire:loading.attr="disabled">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right"
        aria-labelledby="{{ $actionPrefix }}Actions{{ $booking->id }}">
        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bookingViewModal"
            data-id="{{ $booking->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Details
        </a>

        @can('update', \App\Models\Amenity\AmenityBooking::class)
            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#bookingFormModal"
                data-id="{{ $booking->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit Booking
            </a>

            @if ($booking->can_still_be_moved)
                <div class="dropdown-divider"></div>

                @if ($booking->can_be_invoiced)
                    @can('createInvoice', \App\Models\Bill\Bill::class)
                        @can('recordPayment', \App\Models\Amenity\AmenityBooking::class)
                            <a class="dropdown-item" href="#"
                                wire:click.prevent="createInvoice({{ $booking->id }})">
                                <i class="fas fa-fw fa-file-invoice-dollar mr-1"></i> Create Invoice
                            </a>
                        @endcan
                    @endcan
                @endif

                @if ($booking->bill_id)
                    @can('create', \App\Models\Payment::class)
                        <a class="dropdown-item" href="#" data-toggle="modal"
                            data-target="#recordPaymentModal" data-id="{{ $booking->bill_id }}">
                            <i class="fas fa-fw fa-hand-holding-usd mr-1"></i> Record Payment
                        </a>
                    @endcan
                @endif

                @if ($booking->can_be_approved)
                    <a class="dropdown-item" href="#" data-toggle="modal"
                        data-target="#bookingApproveModal" data-id="{{ $booking->id }}">
                        <i class="fas fa-fw fa-check mr-1"></i> Approve Booking
                    </a>
                @endif

                @if ($booking->can_be_completed)
                    <a class="dropdown-item" href="#" data-toggle="modal"
                        data-target="#bookingCompleteModal" data-id="{{ $booking->id }}">
                        <i class="fas fa-fw fa-flag-checkered mr-1"></i> Complete Booking
                    </a>
                @endif


                @if ($booking->deposit_is_open)
                    <a class="dropdown-item" href="#" data-toggle="modal"
                        data-target="#bookingRefundDepositModal" data-id="{{ $booking->id }}">
                        <i class="fas fa-fw fa-undo mr-1"></i> Refund Deposit
                    </a>
                    <a class="dropdown-item" href="#" data-toggle="modal"
                        data-target="#bookingForfeitDepositModal" data-id="{{ $booking->id }}">
                        <i class="fas fa-fw fa-ban mr-1"></i> Forfeit Deposit
                    </a>
                @endif

                @if ($booking->can_be_cancelled)
                    <a class="dropdown-item text-warning" href="#" data-toggle="modal"
                        data-target="#bookingCancelModal" data-id="{{ $booking->id }}">
                        <i class="fas fa-fw fa-times-circle mr-1"></i> Cancel Booking
                    </a>
                @endif
            @endif
        @endcan

        @can('delete', \App\Models\Amenity\AmenityBooking::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#bookingDeleteModal" data-id="{{ $booking->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
