<div>
    {{-- =================================================================== --}}
    {{-- STATS WIDGETS                                                       --}}
    {{-- Isolated component: refreshes on `resident-saved` only.             --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['total'] }}" text="Total Residents"
                icon="fas fa-fw fa-users" theme="primary" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['withAccount'] }}" text="With Account"
                icon="fas fa-fw fa-user-check" theme="success" />
        </div>
        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <x-adminlte-small-box title="{{ $stats['withoutAccount'] }}" text="No Account"
                icon="fas fa-fw fa-user-slash" theme="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-adminlte-small-box title="{{ $stats['active'] }}" text="Active Accounts"
                icon="fas fa-fw fa-user-circle" theme="info" />
        </div>
    </div>
</div>
