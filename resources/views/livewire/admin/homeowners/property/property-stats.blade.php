<div>
    {{-- =================================================================== --}}
    {{-- STAT WIDGETS                                                        --}}
    {{-- Isolated component: refreshes on `property-saved` only.             --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['total'] }}" text="Total Properties"
                icon="fas fa-fw fa-home" theme="primary" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['withOwner'] }}" text="With Homeowner"
                icon="fas fa-fw fa-user-check" theme="success" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['vacant'] }}" text="Vacant"
                icon="fas fa-fw fa-door-open" theme="danger" />
        </div>
        <div class="col-6 col-md-3">
            <x-adminlte-small-box title="{{ $stats['addresses'] }}" text="Addresses"
                icon="fas fa-fw fa-map-marker-alt" theme="info" />
        </div>
    </div>
</div>
