{{-- =================================================================== --}}
{{-- WHICH RESERVATION THIS IS ABOUT                                      --}}
{{-- The dialog names it, so the officer confirms the right one. Shared    --}}
{{-- by every move dialog in the bookings table, so the tone a reservation --}}
{{-- reads in cannot drift from one move to the next.                      --}}
{{-- @param  \App\Models\Amenity\AmenityBooking  $booking --}}
{{-- =================================================================== --}}
<div class="border rounded p-3 mb-3">
    <div class="font-weight-bold">{{ $booking->amenity->name ?? '—' }}</div>
    <div class="text-muted small">{{ $booking->date_time_display }}</div>
    <div class="small mt-1">{{ $booking->property_display }} · {{ $booking->booker_name }}</div>
    <div class="small text-muted">
        Amount {{ $booking->amount_display }}
        @if ($booking->requires_deposit)
            · Deposit {{ $booking->deposit_display }}
        @endif
    </div>
</div>
