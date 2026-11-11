<div>
    <x-adminlte-card>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search admins..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Admin::class)
                    <x-adminlte-button label="New Admin" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#userFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                       --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th style="width:40%;" class="cursor-pointer" wire:click="sortBy('name')">
                            Name
                            @if ($sortField === 'name')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th style="width:25%;" class="cursor-pointer" wire:click="sortBy('email')">
                            Email
                            @if ($sortField === 'email')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th style="width:15%;">Role</th>
                        <th style="width:10%;">Status</th>
                        <th style="width:10%;" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($admins as $admin)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $admin->adminlte_image() }}" class="img-circle elevation-1 mr-2"
                                        style="width:32px; height:32px; object-fit:cover;" alt="{{ $admin->name }}">
                                    <span class="font-weight-medium">{{ $admin->name }}</span>
                                </div>
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <span class="badge badge-{{ $admin->role->badge_theme ?? 'secondary' }}">
                                    {{ $admin->role->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $admin->is_active ? 'success' : 'warning' }}">
                                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="btn-group btn-group-sm" role="group">
                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                        data-toggle="modal" data-target="#userViewModal" data-id="{{ $admin->id }}"
                                        class="btn-xs" title="View" />
                                    @can('update', $admin)
                                        @if ($admin->id !== auth()->id())
                                            <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                                data-toggle="modal" data-target="#userFormModal" data-id="{{ $admin->id }}"
                                                class="btn-xs" title="Edit" />
                                        @else
                                            <a class="btn btn-primary btn-xs" href="{{ route('settings.profile') }}" title="Edit">
                                                <i class="fas fa-fw fa-edit"></i>
                                            </a>
                                        @endif
                                    @endcan
                                    @can('resetPassword', $admin)
                                        <x-adminlte-button label="" theme="warning" icon="fas fa-fw fa-key"
                                            data-toggle="modal" data-target="#adminResetPasswordModal" data-id="{{ $admin->id }}"
                                            class="btn-xs" title="{{ __('users.reset.title') }}" />
                                    @endcan
                                    @can('toggleActive', $admin)
                                        @if ($admin->is_active)
                                            <x-adminlte-button label="" theme="warning" icon="fas fa-fw fa-ban"
                                                wire:click="toggleActive({{ $admin->id }})" wire:loading.attr="disabled"
                                                class="btn-xs" title="Deactivate" />
                                        @else
                                            <x-adminlte-button label="" theme="success" icon="fas fa-fw fa-check-circle"
                                                wire:click="toggleActive({{ $admin->id }})" wire:loading.attr="disabled"
                                                class="btn-xs" title="Activate" />
                                        @endif
                                    @endcan
                                    @can('delete', $admin)
                                        <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                            data-toggle="modal" data-target="#userDeleteModal" data-id="{{ $admin->id }}"
                                            class="btn-xs" title="Delete" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                                <p class="mb-0">{{ $search ? 'No admins match your search.' : 'No admin accounts found.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS (hidden on large screens)                              --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($admins as $admin)
            <div class="card mb-3 shadow-sm">
                <div class="card-body py-3">
                    {{-- Header row: avatar + name + status --}}
                    <div class="d-flex align-items-center">
                        <img src="{{ $admin->adminlte_image() }}" class="img-circle elevation-1 flex-shrink-0 mr-2"
                            style="width:40px; height:40px; object-fit:cover;" alt="{{ $admin->name }}">
                        <div class="flex-grow-1">
                            <div class="font-weight-bold">{{ $admin->name }}</div>
                            <div class="text-muted small">{{ $admin->email }}</div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="badge badge-{{ $admin->role->badge_theme ?? 'secondary' }} d-block mb-1">
                                {{ $admin->role->name ?? '—' }}
                            </span>
                            <span class="badge badge-{{ $admin->is_active ? 'success' : 'warning' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end mt-3">
                        <div class="btn-group btn-group-sm d-flex mt-2" role="group">
                            <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-eye"
                                data-toggle="modal" data-target="#userViewModal" data-id="{{ $admin->id }}"
                                class="btn-xs flex-fill" title="View" />
                            @can('update', $admin)
                                @if ($admin->id !== auth()->id())
                                    <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                        data-toggle="modal" data-target="#userFormModal" data-id="{{ $admin->id }}"
                                        class="btn-xs flex-fill" title="Edit" />
                                @else
                                    <a class="btn btn-primary btn-xs flex-fill" href="{{ route('settings.profile') }}" title="Edit">
                                        <i class="fas fa-fw fa-edit"></i>
                                    </a>
                                @endif
                            @endcan
                            @can('resetPassword', $admin)
                                <x-adminlte-button label="" theme="warning" icon="fas fa-fw fa-key"
                                    data-toggle="modal" data-target="#adminResetPasswordModal" data-id="{{ $admin->id }}"
                                    class="btn-xs flex-fill" title="{{ __('users.reset.title') }}" />
                            @endcan
                            @can('toggleActive', $admin)
                                @if ($admin->is_active)
                                    <x-adminlte-button label="" theme="warning" icon="fas fa-fw fa-ban"
                                        wire:click="toggleActive({{ $admin->id }})" wire:loading.attr="disabled"
                                        class="btn-xs flex-fill" title="Deactivate" />
                                @else
                                    <x-adminlte-button label="" theme="success" icon="fas fa-fw fa-check-circle"
                                        wire:click="toggleActive({{ $admin->id }})" wire:loading.attr="disabled"
                                        class="btn-xs flex-fill" title="Activate" />
                                @endif
                            @endcan
                            @can('delete', $admin)
                                <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                    data-toggle="modal" data-target="#userDeleteModal" data-id="{{ $admin->id }}"
                                    class="btn-xs flex-fill" title="Delete" />
                            @endcan
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No admins match your search.' : 'No admin accounts found.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination (rendered once for both desktop & mobile) --}}
    @if ($admins->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $admins->links() }}
        </div>
    @endif
</div>
