<div>
    {{-- =================================================================== --}}
    {{-- HEADER                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-md-8 mb-2 mb-md-0">
                <h5 class="mb-0 font-weight-bold">My Bookings</h5>
                <small class="text-muted">
                    Your reservations of the association's amenities, and where each one stands.
                </small>
            </div>

            <div class="col-12 col-md-4 text-right">
                @if ($canBook)
                    <x-adminlte-button label="Request a Booking" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#bookingRequestModal" class="btn-sm" />
                @endif
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th class="align-middle" style="width:26%;">Amenity</th>
                        <th class="align-middle" style="width:18%;">Date &amp; Time</th>
                        <th class="align-middle" style="width:12%;">Status</th>
                        <th class="align-middle" style="width:22%;">Bill</th>
                        <th class="text-right align-middle" style="width:10%;">Amount</th>
                        <th class="text-right align-middle" style="width:12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr wire:key="booking-row-{{ $booking->id }}">
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $booking->amenity->name ?? '—' }}</div>
                                <div class="text-muted small text-truncate">{{ $booking->reference ?: '—' }}</div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="small text-truncate">{{ $booking->date_time_display }}</div>
                                @if ($booking->purpose)
                                    <div class="text-muted small text-truncate">{{ $booking->purpose }}</div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }}">
                                    {{ $booking->status_enum?->label() ?? $booking->status }}
                                </span>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                @if ($booking->has_bill_waiting)
                                    <span class="badge badge-warning">Bill to pay</span>
                                    <div class="text-muted small text-truncate">
                                        {{ $booking->bill_reference_display }} · {{ $booking->bill->balance_display }}
                                    </div>
                                @elseif ($booking->bill)
                                    <span class="badge badge-success">{{ $booking->bill->status_label }}</span>
                                    <div class="text-muted small text-truncate">{{ $booking->bill_reference_display }}</div>
                                @else
                                    <span class="text-muted small">Not billed</span>
                                @endif
                            </td>
                            <td class="text-right align-middle">
                                <div class="font-weight-medium">{{ $booking->amount_display }}</div>
                                <div class="text-muted small">{{ $booking->deposit_display }}</div>
                            </td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.portal.bookings.partials.booking-actions')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="far fa-fw fa-calendar-check fa-2x mb-2"></i>
                                <p class="mb-0">No bookings yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS                                                        --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($bookings as $booking)
            <div class="card shadow-sm mb-2" wire:key="booking-card-{{ $booking->id }}">
                <div class="card-header bg-white py-2">
                    <div class="d-flex justify-content-between align-items-start" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate">{{ $booking->amenity->name ?? '—' }}</div>
                            <div class="text-muted small text-truncate">{{ $booking->reference ?: '—' }}</div>
                        </div>
                        <span class="badge badge-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }} flex-shrink-0">
                            {{ $booking->status_enum?->label() ?? $booking->status }}
                        </span>
                    </div>
                </div>

                <div class="card-body py-3">
                    <div class="text-muted small mb-2">
                        <i class="far fa-fw fa-clock mr-1"></i>{{ $booking->date_time_display }}
                    </div>

                    <div class="d-flex justify-content-between" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small">Amount</div>
                            <div class="font-weight-medium">{{ $booking->amount_display }}</div>
                            <div class="text-muted small">{{ $booking->deposit_display }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small">Bill</div>
                            @if ($booking->has_bill_waiting)
                                <span class="badge badge-warning">Bill to pay</span>
                                <div class="text-muted small">
                                    {{ $booking->bill_reference_display }} · {{ $booking->bill->balance_display }}
                                </div>
                            @elseif ($booking->bill)
                                <span class="badge badge-success">{{ $booking->bill->status_label }}</span>
                                <div class="text-muted small">{{ $booking->bill_reference_display }}</div>
                            @else
                                <span class="text-muted small">Not billed</span>
                            @endif
                        </div>
                    </div>

                    @if ($booking->purpose)
                        <div class="mt-2 small text-muted text-truncate">
                            <i class="fas fa-fw fa-pencil-alt mr-1"></i>{{ $booking->purpose }}
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light py-2 text-right text-nowrap">
                    @include('livewire.portal.bookings.partials.booking-actions')
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <x-adminlte-empty-state icon="far fa-fw fa-calendar-check" title="No Bookings Yet" />
                </div>
            </div>
        @endforelse
    </div>
</div>
