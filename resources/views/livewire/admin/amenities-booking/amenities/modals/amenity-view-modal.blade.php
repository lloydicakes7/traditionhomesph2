<div wire:ignore.self>
    <x-adminlte-modal id="amenityViewModal"
        title="{{ $viewAmenity->name ?? 'Amenity' }}"
        icon="far fa-fw fa-calendar-check" theme="primary" size="lg"
        v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the amenity…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($viewAmenity)
                <img src="{{ $viewAmenity->image_url ?: asset('image/placeholder.jpg') }}"
                    alt="{{ $viewAmenity->name }}" class="w-100 elevation-1 mb-3"
                    style="height:200px; object-fit:fill; border-radius:0.25rem;">

                @if ($viewAmenity->description)
                    <p class="text-muted mb-3">{{ $viewAmenity->description }}</p>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Rate</div>
                            <div class="font-weight-bold">{{ $viewAmenity->rate_display }}</div>
                            <div class="text-muted small">{{ $viewAmenity->rate_basis_display }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Homeowner Portal</div>
                            <div class="font-weight-bold">{{ $viewAmenity->portal_status_label }}</div>
                            <div class="text-muted small">{{ $viewAmenity->portal_status_note }}</div>
                        </div>
                    </div>

                </div>

                <div class="mb-1">
                    <div class="font-weight-bold">Weekly Schedule</div>
                    <div class="text-muted small mb-2">{{ $viewAmenity->schedule_summary }}</div>

                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach ($viewAmenity->schedule_rows as $row)
                                <tr>
                                    <td class="pl-0" style="width:40%;">{{ $row['day'] }}</td>
                                    <td class="{{ $row['is_closed'] ? 'text-muted' : '' }}">
                                        {{ $row['hours'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="primary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
