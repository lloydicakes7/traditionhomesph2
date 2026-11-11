<div>

    {{-- =================================================================== --}}
    {{-- SEARCH                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row">
            <div class="col-12 col-md-6 col-lg-4">
                <x-adminlte-input name="search" placeholder="Search by name, email, mobile no..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="align-middle">Applicant</th>
                        <th class="align-middle" style="width:15%;">Mobile No.</th>
                        <th class="align-middle" style="width:15%;">Registered</th>
                        <th class="align-middle text-center" style="width:12%;">Valid ID</th>
                        <th class="text-right align-middle" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $registration)
                        <tr wire:key="pending-row-{{ $registration->id }}">
                            <td class="align-middle">
                                <div class="d-flex align-items-center" style="gap:0.75rem;">
                                    @if ($registration->resident?->photo_url)
                                        <img src="{{ $registration->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;" loading="lazy">
                                    @else
                                        <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;" loading="lazy">
                                    @endif
                                    <div class="min-width-0">
                                        <span class="font-weight-medium">
                                            {{ $registration->resident?->full_name ?? 'No profile on file' }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $registration->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <small>{{ $registration->resident?->mobile_no ?? 'None' }}</small>
                            </td>
                            <td class="align-middle">
                                <small class="text-muted">
                                    {{ $registration->created_at ? $registration->created_at->format('M d, Y') : '—' }}
                                </small>
                            </td>
                            <td class="text-center align-middle">
                                @if ($registration->resident?->valid_id_path)
                                    @can('viewValidId', $registration->resident)
                                        <a href="{{ route('homeowners.residents.valid-id', $registration->resident) }}"
                                            class="btn btn-outline-primary btn-xs">
                                            <i class="fas fa-fw fa-download mr-1"></i>View
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endcan
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td class="text-right align-middle text-nowrap" style="overflow: visible;">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="pendingActions{{ $registration->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="pendingActions{{ $registration->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                            data-target="#pendingRegistrationViewModal" data-id="{{ $registration->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Details
                                        </a>

                                        @can('update', \App\Models\User::class)
                                            <a class="dropdown-item text-success" href="#"
                                                wire:click.prevent="accept({{ $registration->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="fas fa-fw fa-check-circle mr-1"></i> Accept Registration
                                            </a>
                                        @endcan

                                        @can('delete', \App\Models\User::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                                                data-target="#homeownerDeleteModal" data-id="{{ $registration->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-user-clock fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search ? 'No registrations match your search.' : 'No registrations are waiting for approval.' }}
                                </p>
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
        @forelse ($registrations as $registration)
            <div class="card mb-3 shadow-sm" wire:key="pending-card-{{ $registration->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-center" style="gap:0.6rem;">
                        @if ($registration->resident?->photo_url)
                            <img src="{{ $registration->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                style="width:40px; height:40px; object-fit:cover;" loading="lazy">
                        @else
                            <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                style="width:40px; height:40px; object-fit:cover;" loading="lazy">
                        @endif
                        <div class="min-width-0">
                            <span class="font-weight-bold d-block text-truncate" style="font-size:0.95rem;">
                                {{ $registration->resident?->full_name ?? 'No profile on file' }}
                            </span>
                            <span class="badge badge-warning">Waiting for approval</span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="d-flex flex-column mb-2">
                        <span class="small text-truncate">
                            <i class="fas fa-fw fa-envelope text-muted mr-1" style="width:14px;"></i>{{ $registration->email }}
                        </span>
                        @if ($registration->resident?->mobile_no)
                            <span class="small text-nowrap">
                                <i class="fas fa-fw fa-mobile-alt text-muted mr-1" style="width:14px;"></i>{{ $registration->resident->mobile_no }}
                            </span>
                        @endif
                        <span class="small text-muted">
                            <i class="fas fa-fw fa-calendar text-muted mr-1" style="width:14px;"></i>
                            Registered {{ $registration->created_at ? $registration->created_at->format('M d, Y') : '—' }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:0.5rem;">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal"
                            data-target="#pendingRegistrationViewModal" data-id="{{ $registration->id }}">
                            <i class="fas fa-fw fa-eye mr-1"></i>View Details
                        </button>

                        @can('update', \App\Models\User::class)
                            <button type="button" class="btn btn-outline-success btn-sm"
                                wire:click="accept({{ $registration->id }})" wire:loading.attr="disabled">
                                <i class="fas fa-fw fa-check-circle mr-1"></i>Accept
                            </button>
                        @endcan

                        @can('delete', \App\Models\User::class)
                            <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal"
                                data-target="#homeownerDeleteModal" data-id="{{ $registration->id }}">
                                <i class="fas fa-fw fa-trash mr-1"></i>Delete
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-user-clock fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search ? 'No registrations match your search.' : 'No registrations are waiting for approval.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($registrations->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $registrations->links() }}
        </div>
    @endif

</div>
