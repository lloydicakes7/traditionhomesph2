<div>
    {{-- =================================================================== --}}
    {{-- MONTH HEADER + STATUS LEGEND                                        --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 mb-2 mb-sm-0 d-flex align-items-center gap-2">
                <x-adminlte-button theme="secondary" icon="fas fa-fw fa-chevron-left"
                    wire:click="previousMonth" wire:loading.attr="disabled" class="btn-sm" title="Previous month" />
                <h5 class="mb-0 font-weight-bold">{{ $gridMonth->format('F Y') }}</h5>
                <x-adminlte-button theme="secondary" icon="fas fa-fw fa-chevron-right"
                    wire:click="nextMonth" wire:loading.attr="disabled" class="btn-sm" title="Next month" />
                <x-adminlte-button label="Today" theme="secondary"
                    wire:click="currentMonth" wire:loading.attr="disabled" class="btn-sm" />
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Amenity\AmenityBooking::class)
                    <x-adminlte-button label="Add Booking" theme="primary" icon="fas fa-fw fa-plus"
                            data-toggle="modal" data-target="#bookingFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MONTH GRID — large screens only                                      --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr class="text-center">
                        @foreach ($weekdays as $label)
                            <th class="small text-muted">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($weeks as $week)
                        <tr>
                            @foreach ($week as $day)
                                <td style="width:14.28%; height:120px; vertical-align:top;"
                                    class="{{ $day->month !== $gridMonth->month ? 'bg-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small {{ $day->month !== $gridMonth->month ? 'text-muted' : 'font-weight-medium' }}">
                                            {{ $day->format('j') }}
                                        </span>
                                        @if ($day->isToday())
                                            <span class="badge badge-primary">Today</span>
                                        @endif
                                    </div>

                                    {{-- The day's reservations --}}
                                    @foreach ($byDay->get($day->format('Y-m-d'), collect()) as $booking)
                                        <button type="button"
                                            class="btn btn-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }} btn-block text-left px-1 py-0 mb-1"
                                            data-toggle="modal" data-target="#bookingViewModal"
                                            data-id="{{ $booking->id }}"
                                            title="{{ $booking->booker_name }} — {{ $booking->amenity->name ?? '' }}">
                                            <small>
                                                {{ $booking->time_range_display }}
                                                {{ $booking->amenity->name ?? '' }}
                                            </small>
                                        </button>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE AGENDA — phones and tablets                                   --}}
    {{-- =================================================================== --}}
    <div class="d-lg-none">
        @forelse ($byDay as $date => $dayBookings)
            @php $day = \Illuminate\Support\Carbon::parse($date); @endphp
            <div class="card shadow-sm mb-2" wire:key="booking-day-{{ $date }}">
                <div class="card-header bg-white py-2">
                    <div class="d-flex justify-content-between">
                        <span class="small font-weight-bold">
                            {{ $day->format('D, M j') }}
                            @if ($day->isToday())
                                <span class="badge badge-primary ml-1">Today</span>
                            @endif
                        </span>
                        <span class="badge badge-secondary">{{ $dayBookings->count() }}</span>
                    </div>

                </div>

                <div class="card-body py-2">
                    @foreach ($dayBookings as $booking)
                        <button type="button"
                            class="btn btn-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }} btn-sm btn-block text-left px-2 py-1 {{ $loop->last ? 'mb-0' : 'mb-1' }}"
                            data-toggle="modal" data-target="#bookingViewModal"
                            data-id="{{ $booking->id }}"
                            title="{{ $booking->booker_name }} — {{ $booking->amenity->name ?? '' }}">
                            <small>
                                {{ $booking->time_range_display }}
                                {{ $booking->amenity->name ?? '' }}
                                <span class="d-block">{{ $booking->booker_name }}</span>
                            </small>
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="far fa-fw fa-calendar-check fa-2x mb-2"></i>
                    <p class="mb-0">No reservations in {{ $gridMonth->format('F Y') }}.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
