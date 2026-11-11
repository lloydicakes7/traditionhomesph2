<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + ADD BUTTON                                       --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search by property, homeowner, guest..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-2 mb-md-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-2 mb-md-0">
                <x-adminlte-select name="filterAmenity" wire:model="filterAmenity" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Amenities</option>
                    @foreach ($amenities as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-4 col-md-3 col-lg-2 mb-2 mb-md-0">
                <x-adminlte-select name="filterDeposit" wire:model="filterDeposit" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Deposits</option>
                    @foreach ($depositStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-md-3 col-lg-auto ml-auto">
                @can('create', \App\Models\Amenity\AmenityBooking::class)
                    <x-adminlte-button label="Add Booking" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#bookingFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MASS DELETE BAR                                                     --}}
    {{-- =================================================================== --}}
    @if (!empty($selectedIds))
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="text-muted small font-weight-bold">
                <i class="fas fa-fw fa-check-square"></i>
                {{ count($selectedIds) }} selected
            </span>
            @can('delete', \App\Models\Amenity\AmenityBooking::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#bookingMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table id="amenityBookingsTable" class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th class="align-middle" style="width:24%;">
                            <div class="d-flex align-items-center">
                                @can('delete', \App\Models\Amenity\AmenityBooking::class)
                                    <div class="custom-control custom-checkbox mr-2">
                                        <input type="checkbox" class="custom-control-input" id="selectAll"
                                            wire:model="selectAll">
                                        <label class="custom-control-label" for="selectAll"></label>
                                    </div>
                                @endcan
                                <span class="cursor-pointer" wire:click="sortBy('starts_at')">
                                    Date &amp; Time
                                    @if ($sortField === 'starts_at')
                                        <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('amenity')" style="width:16%;">
                            Amenity
                            @if ($sortField === 'amenity')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:23%;">Booked For</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('amount')" style="width:22%;">
                            Amount / Deposit
                            @if ($sortField === 'amount')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="d-flex align-items-start">
                                    @can('delete', \App\Models\Amenity\AmenityBooking::class)
                                        <div class="custom-control custom-checkbox mr-2 mt-1">
                                            <input type="checkbox" class="custom-control-input"
                                                id="booking-{{ $booking->id }}" value="{{ $booking->id }}"
                                                wire:model="selectedIds">
                                            <label class="custom-control-label" for="booking-{{ $booking->id }}"></label>
                                        </div>
                                    @endcan
                                    <div class="min-width-0">
                                        <div class="font-weight-medium text-truncate">{{ $booking->date_time_display }}</div>
                                        <div class="text-muted small text-truncate">{{ $booking->reference ?: '—' }}</div>
                                        <div class="small">
                                            <span class="badge badge-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }}">
                                                {{ $booking->status_enum?->label() ?? $booking->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="small text-truncate">{{ $booking->amenity->name ?? '—' }}</div>
                                @if ($booking->purpose)
                                    <div class="text-muted small text-truncate">{{ $booking->purpose }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">{{ $booking->booker_name }}</div>
                                <div class="small text-muted text-truncate">{{ $booking->property_display }}</div>
                            </td>
                            <td class="align-middle text-right">
                                <span class="font-weight-medium">{{ $booking->amount_display }}</span>
                                <div class="text-muted small">
                                    {{ $booking->bill_id ? $booking->bill_reference_display : 'Not billed' }}
                                </div>
                                <div class="text-muted small mt-1">{{ $booking->deposit_display }}</div>
                                <div class="text-muted small">{{ $booking->deposit_status_display }}</div>
                            </td>
                            <td class="align-middle text-center">
                                @include('livewire.admin.amenities-booking.bookings.partials.booking-actions', [
                                    'actionPrefix' => 'booking',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="far fa-fw fa-calendar-check fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterAmenity || $filterDeposit
                                        ? 'No bookings match your filters.'
                                        : 'No reservations yet. Homeowners ask for a facility from the portal, or the office books it here.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE / TABLET CARDS                                               --}}
    {{-- Same record as the desktop row, laid out as a stacked card: the       --}}
    {{-- header carries what the row is (its window, its facility and the      --}}
    {{-- status it is in), the body the facts underneath it, the footer the    --}}
    {{-- moves — so nothing is truncated or squeezed on a narrow screen.       --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($bookings as $booking)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Amenity\AmenityBooking::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-booking-{{ $booking->id }}" value="{{ $booking->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-booking-{{ $booking->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $booking->date_time_display }}
                                </div>
                                <div class="small text-muted text-truncate">{{ $booking->amenity->name ?? '—' }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $booking->status_enum?->badgeTheme() ?? 'secondary' }} flex-shrink-0">
                            {{ $booking->status_enum?->label() ?? $booking->status }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="mb-2">
                        <div class="small text-truncate">{{ $booking->property_display }}</div>
                        <div class="font-weight-medium text-truncate">{{ $booking->booker_name }}</div>
                        <div class="text-muted small text-truncate">{{ $booking->reference ?: '—' }}</div>
                        @if ($booking->purpose)
                            <div class="text-muted small text-truncate">{{ $booking->purpose }}</div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                            <div class="small font-weight-medium">{{ $booking->amount_display }}</div>
                            <div class="text-muted small">
                                {{ $booking->bill_id ? $booking->bill_reference_display : 'Not billed' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">DEPOSIT</div>
                            <div class="small font-weight-medium">{{ $booking->deposit_display }}</div>
                            <div class="text-muted small">{{ $booking->deposit_status_display }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    @include('livewire.admin.amenities-booking.bookings.partials.booking-actions', [
                        'actionPrefix' => 'mobBooking',
                    ])
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="far fa-fw fa-calendar-check fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterAmenity || $filterDeposit
                            ? 'No bookings match your filters.'
                            : 'No reservations yet. Homeowners ask for a facility from the portal, or the office books it here.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($bookings->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
