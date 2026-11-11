<div>
    {{-- =================================================================== --}}
    {{-- STATS WIDGETS                                                       --}}
    {{-- Isolated component: refreshes on `household-saved` only.            --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['total'] }}" text="Total Households"
                icon="fas fa-fw fa-users" theme="primary" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['owners'] }}" text="Owners"
                icon="fas fa-fw fa-user-tie" theme="success" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['tenants'] }}" text="Renters"
                icon="fas fa-fw fa-user-friends" theme="info" />
        </div>
        <div class="col-6 col-md-3">
            <x-adminlte-small-box title="{{ $stats['others'] }}" text="Caretaker / Others"
                icon="fas fa-fw fa-user-cog" theme="secondary" />
        </div>
    </div>
</div>
