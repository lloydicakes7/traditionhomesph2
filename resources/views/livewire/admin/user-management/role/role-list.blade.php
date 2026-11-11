<div>
    {{-- Header with Create button --}}
    <x-adminlte-card>
        <div class="d-flex justify-content-end align-items-center flex-wrap">
            @can('manageRoles', \App\Models\Admin::class)
                <x-adminlte-button label="New Role" theme="primary" icon="fas fa-fw fa-plus"
                    data-toggle="modal" data-target="#roleFormModal" class="btn-sm" />
            @endcan
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE (hidden on small screens)                             --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:40%;">Role</th>
                        <th style="width:30%;">Description</th>
                        <th style="width:10%;" class="text-center">Users</th>
                        <th style="width:10%;" class="text-center">Permissions</th>
                        <th style="width:10%;" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $role->badge_theme }}">{{ $role->name }}</span>
                                @if ($role->is_system)
                                    <span class="badge badge-info ml-1" title="The software runs on this role">System</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $role->description ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $role->admins_count > 0 ? 'primary' : 'secondary' }}">
                                    {{ $role->admins_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if ($role->hasFullAccess())
                                    <span class="badge badge-info" title="Every permission in the catalogue">All</span>
                                @else
                                    <span class="badge badge-info">{{ $role->permissions_count }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('manageRoles', \App\Models\Admin::class)
                                    <div class="btn-group btn-group-sm" role="group">
                                        <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                            data-toggle="modal" data-target="#roleFormModal" data-id="{{ $role->id }}"
                                            class="btn-xs" title="Edit" />
                                        @if ($role->is_protected)
                                            <button type="button" class="btn btn-secondary btn-xs"
                                                title="One of the association's own roles">
                                                <i class="fas fa-fw fa-lock"></i>
                                            </button>
                                        @else
                                            <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                                data-toggle="modal" data-target="#roleDeleteModal" data-id="{{ $role->id }}"
                                                class="btn-xs" title="Delete" />
                                        @endif
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-shield-alt fa-2x mb-2"></i>
                                <p class="mb-0">No roles found.</p>
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
        @forelse ($roles as $role)
            <div class="card mb-3 shadow-sm">
                <div class="card-body py-3">
                    {{-- Header row: role name + system badge + counts --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge badge-{{ $role->badge_theme }}">{{ $role->name }}</span>
                            @if ($role->is_system)
                                <span class="badge badge-info ml-1">System</span>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="badge badge-{{ $role->admins_count > 0 ? 'primary' : 'secondary' }} mr-1"
                                title="Users">
                                <i class="fas fa-fw fa-users"></i> {{ $role->admins_count }}
                            </span>
                            <span class="badge badge-info" title="Permissions">
                                <i class="fas fa-fw fa-key"></i>
                                {{ $role->hasFullAccess() ? 'All' : $role->permissions_count }}
                            </span>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="text-muted small mb-3">
                        {{ $role->description ?? 'No description provided.' }}
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end">
                        <div class="btn-group btn-group-sm d-flex" role="group">
                            @can('manageRoles', \App\Models\Admin::class)
                                <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                    data-toggle="modal" data-target="#roleFormModal" data-id="{{ $role->id }}"
                                    class="btn-xs flex-fill" title="Edit" />
                                @if ($role->is_protected)
                                    <button type="button" class="btn btn-secondary btn-xs flex-fill"
                                        title="One of the association's own roles">
                                        <i class="fas fa-fw fa-lock"></i>
                                    </button>
                                @else
                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                        data-toggle="modal" data-target="#roleDeleteModal" data-id="{{ $role->id }}"
                                        class="btn-xs flex-fill" title="Delete" />
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-shield-alt fa-2x mb-2"></i>
                    <p class="mb-0">No roles found.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination (rendered once for both desktop & mobile) --}}
    @if ($roles->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $roles->links() }}
        </div>
    @endif
</div>
