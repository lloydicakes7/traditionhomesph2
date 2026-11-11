<div>

    {{-- Row legend: why a row is tinted. --}}
    <div class="d-flex flex-wrap justify-content-start gap-1 my-2 small text-muted">
        <span class="mr-3"><span class="badge badge-warning">&nbsp;</span> Pending request</span>
        <span class="mr-3"><span class="badge badge-danger">&nbsp;</span> Active violation</span>
        <span><span class="badge badge-info">&nbsp;</span> Unpaid bill</span>
    </div>

    {{-- =================================================================== --}}
    {{-- SEARCH + ADD BUTTON CARD                                            --}}
    {{-- =================================================================== --}}

    <x-adminlte-card>
        <div class="row justify-content-center">
            <div class="col-12 col-md-5 col-lg-4 mb-2 mb-md-0">
                <x-adminlte-input name="search" placeholder="Search by name, email, mobile no..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-md-4 col-lg-3 mb-2 mb-md-0">
                <x-adminlte-select name="accountStatus" wire:model.lazy="accountStatus"
                    igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-user-check"></i></div>
                    </x-slot>
                    <option value="">All Accounts</option>
                    <option value="active">Activated</option>
                    <option value="inactive">Deactivated</option>
                </x-adminlte-select>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\User::class)
                    <x-adminlte-button label="Add Homeowner" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#homeownerFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\User::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#homeownerMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        @can('delete', \App\Models\User::class)
                            <th style="width:30px;" class="text-center align-middle">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('email')">
                            Homeowner
                            @if ($sortField === 'email')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width:15%;">Mobile No.</th>
                        <th class="text-center align-middle" style="width:10%;">Members</th>
                        <th class="align-middle" style="width:15%;">Last Login</th>
                        <th style="width:150px;" class="text-right align-middle">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="homeowner-row-{{ $user->id }}"
                            class="{{ $user->row_alert ? 'table-' . $user->row_alert : '' }}">
                            @can('delete', \App\Models\User::class)
                            <td class="text-center align-middle">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input"
                                        id="ho-{{ $user->id }}" value="{{ $user->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="ho-{{ $user->id }}"></label>
                                </div>
                            </td>
                            @endcan
                            <td class="align-middle">
                                <div class="d-flex align-items-center" style="gap:0.75rem;">
                                    @if ($user->resident?->photo_url)
                                        <img src="{{ $user->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;">
                                    @else
                                        <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                            style="width:38px; height:38px; object-fit:cover;">
                                    @endif
                                    <div class="min-width-0">
                                        @if($user->resident?->full_name)
                                            <span class="font-weight-medium">{{ $user->resident->full_name }}</span><br>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        @else
                                            <span class="font-weight-medium">{{ $user->email }}</span>
                                        @endif
                                        <div class="small">
                                            @if ($user->is_active)
                                                <span class="text-success">
                                                    <i class="fas fa-fw fa-check-circle mr-1"></i>Activated
                                                </span>
                                            @else
                                                <span class="text-danger">
                                                    <i class="fas fa-fw fa-ban mr-1"></i>Disabled
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                @if ($user->resident?->mobile_no)
                                    <small>{{ $user->resident->mobile_no }}</small>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <span class="font-weight-medium">{{ $user->member_count ?? 0 }}</span>
                            </td>
                            <td class="align-middle">
                                <small class="text-muted">
                                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                                </small>
                            </td>
                            <td class="text-right align-middle text-nowrap" style="overflow: visible;">
                                <x-customs.homeowner-action-buttons :user="$user" :resident="$user->resident" variant="desktop" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                                <p class="mb-0">{{ $search ? 'No homeowners match your search.' : 'No homeowners added yet.' }}</p>
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
        @forelse ($users as $user)
            <div class="card mb-3 shadow-sm {{ $user->row_alert ? 'table-' . $user->row_alert : '' }}"
                wire:key="homeowner-card-{{ $user->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-center" style="gap:0.6rem;">
                        @can('delete', \App\Models\User::class)
                            <div class="custom-control custom-checkbox flex-shrink-0">
                                <input type="checkbox" class="custom-control-input"
                                    id="mob-ho-{{ $user->id }}" value="{{ $user->id }}"
                                    wire:model="selectedIds">
                                <label class="custom-control-label" for="mob-ho-{{ $user->id }}"></label>
                            </div>
                        @endcan
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            @if ($user->resident?->photo_url)
                                <img src="{{ $user->resident->photo_url }}" class="rounded-circle flex-shrink-0"
                                    style="width:40px; height:40px; object-fit:cover;">
                            @else
                                <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                    style="width:40px; height:40px; object-fit:cover;">
                            @endif
                                @if($user->resident?->full_name)
                                    <span class="font-weight-bold" style="font-size:0.95rem;">
                                        {{ $user->resident?->full_name ?? $user->name }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:0.95rem;">
                                        No record yet
                                    </span>
                                @endif
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @if ($user->email || $user->resident?->mobile_no)
                        <div class="d-flex flex-column flex-wrap gap-1 mb-2">
                            @if ($user->email)
                                <span class="small" style="word-break:break-all;">
                                    <i class="fas fa-fw fa-envelope text-muted mr-1" style="width:14px;"></i>{{ $user->email }}
                                </span>
                            @endif
                            @if ($user->is_active)
                                <span class="small text-success">
                                    <i class="fas fa-fw fa-check-circle mr-1" style="width:14px;"></i>Activated
                                </span>
                            @else
                                <span class="small text-danger">
                                    <i class="fas fa-fw fa-ban mr-1" style="width:14px;"></i>Disabled
                                </span>
                            @endif
                            @if ($user->resident?->mobile_no)
                                <span class="small text-nowrap">
                                    <i class="fas fa-fw fa-mobile-alt text-muted mr-1" style="width:14px;"></i>{{ $user->resident->mobile_no }}
                                </span>
                            @endif
                        </div>
                    @endif
                    <div class="small text-muted" style="font-size:0.7rem;">
                        <i class="fas fa-fw fa-clock mr-1"></i>
                        Last login: {{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}
                    </div>
                </div>
                <div class="p-3">
                    <x-customs.homeowner-action-buttons :user="$user" :resident="$user->resident" variant="mobile" />
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No homeowners match your search.' : 'No homeowners added yet.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $users->links() }}
        </div>
    @endif

</div>
