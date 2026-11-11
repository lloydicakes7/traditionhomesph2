<div wire:ignore.self>
    <x-adminlte-modal id="bookingViewModal" title="Booking Details"
        icon="far fa-fw fa-calendar-check" theme="primary" size="lg" v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        @if ($viewBooking)
            {{-- Header: what was booked, when, and where it stands --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-0 font-weight-bold">{{ $viewBooking->amenity->name ?? '—' }}</h5>
                    <small class="text-muted d-block">{{ $viewBooking->reference ?: '—' }}</small>
                    <small class="text-muted">
                        <i class="far fa-fw fa-clock mr-1"></i>{{ $viewBooking->date_time_display }}
                    </small>
                </div>
                <span class="badge badge-{{ $viewBooking->status_enum?->badgeTheme() ?? 'secondary' }}">
                    {{ $viewBooking->status_enum?->label() ?? $viewBooking->status }}
                </span>
            </div>

            {{-- What it owes, and the deposit tied to it --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Amount</div>
                        <div class="font-weight-bold">{{ $viewBooking->amount_display }}</div>
                        <div class="text-muted small">{{ $viewBooking->amenity->rate_basis_display ?? '' }}</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Deposit</div>
                        <div class="font-weight-bold">{{ $viewBooking->deposit_display }}</div>
                        <div class="text-muted small">{{ $viewBooking->deposit_status_display }}</div>
                    </div>
                </div>
            </div>

            {{-- The reservation's details --}}
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="text-muted pl-0" style="width:35%;">Property</td>
                        <td>{{ $viewBooking->property_display }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-0">Booked for</td>
                        <td>{{ $viewBooking->booker_name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-0">Bill #</td>
                        <td>{{ $viewBooking->bill_reference_display }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted pl-0">Filed by</td>
                        <td>{{ $viewBooking->createdByAdmin?->name ?? 'Homeowner portal' }}</td>
                    </tr>
                    @if ($viewBooking->purpose)
                        <tr>
                            <td class="text-muted pl-0">Purpose</td>
                            <td>{{ $viewBooking->purpose }}</td>
                        </tr>
                    @endif
                    @if ($viewBooking->remarks)
                        <tr>
                            <td class="text-muted pl-0">Remarks</td>
                            <td>{{ $viewBooking->remarks }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            {{-- The timeline: submission → approval → payment → deposit →
                 completion (or cancellation) --}}
            <div class="font-weight-bold mb-2">Reservation Timeline</div>
            <ul class="list-unstyled mb-0">
                @foreach ($viewBooking->timeline() as $step)
                    <li class="d-flex mb-2">
                        <i class="{{ $step['icon'] }} mt-1 mr-2"></i>
                        <div>
                            <div class="font-weight-medium">{{ $step['label'] }}</div>
                            <small class="text-muted">
                                {{ $step['when'] }}@if ($step['note']) · {{ $step['note'] }}@endif
                            </small>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="primary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
