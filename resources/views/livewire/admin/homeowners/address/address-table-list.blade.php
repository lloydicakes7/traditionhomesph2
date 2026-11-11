<div>
    {{-- =================================================================== --}}
    {{-- HEADER WITH CREATE BUTTON                                           --}}
    {{-- =================================================================== --}}
    @can('create', \App\Models\Address::class)
        <x-adminlte-card>
            <div class="row justify-content-center">
                <div class="col-12 col-sm-auto ml-auto">
                    <x-adminlte-button label="New Address" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#addressFormModal" class="btn-sm btn-block" />
                </div>
            </div>
        </x-adminlte-card>
    @endcan

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th style="width:15%;" class="align-middle">Label</th>
                        <th class="align-middle">Address</th>
                        <th style="width:10%;" class="align-middle">ZIP</th>
                        <th style="width:12%;" class="align-middle">Country</th>
                        <th style="width:130px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addresses as $address)
                        <tr wire:key="address-row-{{ $address->id }}">
                            <td class="font-weight-bold align-middle">
                                {{ $address->label }}
                                @if ($address->is_primary)
                                    <span class="badge badge-primary ml-1">Primary</span>
                                @endif
                            </td>
                            <td class="text-muted small align-middle">
                                @if ($address->street_line)
                                    <div>{{ $address->street_line }}</div>
                                @endif
                                <div>{{ $address->city_line }}</div>
                            </td>
                            <td class="align-middle">{{ $address->zip_code ?? '—' }}</td>
                            <td class="align-middle">{{ $address->country }}</td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.admin.homeowners.address.partials.address-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-map-marked-alt fa-2x mb-2"></i>
                                <p class="mb-0">No addresses added yet.</p>
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
        @forelse ($addresses as $address)
            <div class="card mb-2 shadow-sm" wire:key="address-card-{{ $address->id }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="font-weight-bold">{{ $address->label }}</span>
                        @if ($address->is_primary)
                            <span class="badge badge-success"><i class="fas fa-fw fa-check-circle"></i> Primary</span>
                        @endif
                    </div>
                    <div class="text-muted small mb-3">
                        @if ($address->street_line)
                            <div>{{ $address->street_line }}</div>
                        @endif
                        <div>{{ $address->city_line }}</div>
                        <div>{{ $address->country }}</div>
                    </div>
                    <div class="float-right">
                        @include('livewire.admin.homeowners.address.partials.address-actions', ['actionKey' => 'Mobile'])
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-map-marked-alt fa-2x mb-2"></i>
                    <p class="mb-0">No addresses added yet.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($addresses->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $addresses->links() }}
        </div>
    @endif
</div>
