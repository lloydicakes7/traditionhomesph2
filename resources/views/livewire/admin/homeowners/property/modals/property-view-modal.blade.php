<div wire:ignore.self>
    <x-adminlte-modal id="propertyViewModal"
        title="Property Details"
        icon="fas fa-fw fa-home" theme="info" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading property details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($viewProperty)
                <div class="text-center mb-4">
                    <img src="{{ $viewProperty->photo_url ?: asset('image/house.png') }}"
                        class="img-fluid rounded shadow-sm elevation-1"
                        style="max-height:220px; width:auto; object-fit:cover; border:3px solid #fff;">
                </div>

                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" style="width:30%;">Block / Lot</td><td class="font-weight-medium">{{ $viewProperty->block_lot_label }}</td></tr>
                    <tr><td class="text-muted">Address</td><td>{{ $viewProperty->address?->full_address ?? '—' }}</td></tr>
                    <tr><td class="text-muted">SQM</td><td>{{ $viewProperty->sqm_type_display }}</td></tr>
                    <tr><td class="text-muted">Type</td><td>{{ $viewProperty->type_enum?->label() ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Homeowner</td>
                        <td>
                            @if ($viewProperty->owner)
                                {{ $viewProperty->owner->name }}
                                <br><small class="text-muted"><i class="fas fa-fw fa-envelope mr-1"></i>{{ $viewProperty->owner->email ?? '—' }}</small>
                                @if ($viewProperty->owner->resident && $viewProperty->owner->resident->mobile_no)
                                    <br><small class="text-muted"><i class="fas fa-fw fa-phone mr-1"></i>{{ $viewProperty->owner->resident->mobile_no }}</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if ($viewProperty->currentHousehold)
                    <div class="mt-3">
                        <h6 class="font-weight-bold"><i class="fas fa-fw fa-id-card mr-1"></i>RBI Household Record</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:35%;">Household</td>
                                <td>{{ $viewProperty->currentHousehold->rbi_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tenure</td>
                                <td>{{ $viewProperty->currentHousehold->tenure_label }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Families / Members</td>
                                <td>
                                    {{ (int) ($viewProperty->currentHousehold->families_count ?: 1) }}
                                    / {{ $viewProperty->currentHousehold->memberRecords->count() }}
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif

                @if ($viewProperty->currentHousehold && $viewProperty->currentHousehold->prev_address)
                    <div class="mt-3">
                        <h6 class="font-weight-bold"><i class="fas fa-fw fa-map-marked-alt mr-1"></i>Previous Address</h6>
                        <p class="text-muted small mb-0">{{ $viewProperty->currentHousehold->prev_address }}</p>
                    </div>
                @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
