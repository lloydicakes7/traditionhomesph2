<div wire:ignore.self>
    <x-adminlte-modal id="roleFormModal"
        title="{{ $isEditing ? 'Edit Role' : 'Create New Role' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-user-shield' : 'fa-plus-circle' }}"
        theme="primary" size="xl" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the role…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form onsubmit="return false;">
            {{-- ============================================================= --}}
            {{-- THE ROLE                                                       --}}
            {{-- ============================================================= --}}
            <div class="row">
                <div class="col-12 col-md-7">
                    @if ($permissionsLocked)
                        <x-adminlte-input name="form.name" label="Role Name" placeholder="e.g., Secretary"
                            wire:model.defer="form.name" igroup-size="sm" disabled readonly>
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                        </x-adminlte-input>
                    @else
                        <x-adminlte-input name="form.name" label="Role Name" placeholder="e.g., Secretary"
                            wire:model.defer="form.name" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                        </x-adminlte-input>
                    @endif
                </div>
                <div class="col-12 col-md-5">
                    <x-adminlte-input name="form.description" label="Description" placeholder="What this role is for"
                        wire:model.defer="form.description" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-align-left"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>

            @if ($permissionsLocked)
                <div class="callout callout-info mb-0">
                    <h6 class="mb-1"><i class="fas fa-fw fa-shield-alt mr-1"></i> Full access</h6>
                    <p class="mb-0 small">
                        The Administrator holds every permission the panel declares, and always will —
                        the catalogue in code decides what that is, so there is nothing to switch here.
                        Create or edit another role to grant a narrower set.
                    </p>
                </div>
            @else
                {{-- ============================================================= --}}
                {{-- WHAT IT MAY DO                                                 --}}
                {{-- ============================================================= --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <label class="mb-0"><i class="fas fa-fw fa-key mr-1"></i> Permissions</label>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-{{ $selectedCount ? 'info' : 'secondary' }} mr-2">
                            {{ $selectedCount }} of {{ $totalCount }} granted
                        </span>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="grantAll"
                            wire:loading.attr="disabled">Grant all</button>
                        @if ($selectedCount)
                            <button type="button" class="btn btn-secondary btn-sm ml-2"
                                wire:click="revokeAll" wire:loading.attr="disabled">Clear all</button>
                        @endif
                    </div>
                </div>

                <x-adminlte-input name="permissionSearch" placeholder="Search a module or an action…"
                    wire:model.debounce.500ms="permissionSearch" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <div class="row">
                        {{-- ========================================================= --}}
                        {{-- THE CATALOGUE, GROUPED AS THE MENUS GROUP IT               --}}
                        {{-- ========================================================= --}}
                        <div class="col-12 col-md-5 d-flex flex-column mb-3 mb-md-0">
                            <div class="card">
                                <div class="card-header py-2">
                                    <h6 class="mb-0"><i class="fas fa-fw fa-sitemap mr-1"></i> Modules</h6>
                                </div>

                                <div class="overflow-auto" style="max-height: 22rem;">
                                    <div class="list-group list-group-flush rounded-0">
                                        @forelse ($catalogue as $group => $modules)
                                            <div class="list-group-item py-1 px-3 bg-light text-uppercase font-weight-bold"
                                                style="font-size: .7rem;">
                                                {{ $group }}
                                            </div>

                                            @foreach ($modules as $module)
                                                <button type="button" wire:key="module-{{ $module['name'] }}"
                                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                    wire:click="selectModule(@js($module['name']))">
                                                    {{ $module['name'] }}
                                                    <span class="badge badge-{{ $module['theme'] }} {{ $module['active'] ? 'badge-light' : '' }}">
                                                        {{ $module['granted'] }}/{{ $module['total'] }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        @empty
                                            <div class="list-group-item text-center text-muted py-4">
                                                <i class="fas fa-fw fa-filter mb-1"></i><br>
                                                Nothing in the catalogue matches “{{ $permissionSearch }}”.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================= --}}
                        {{-- THE CLICKED MODULE'S ACTIONS                               --}}
                        {{-- ========================================================= --}}
                        <div class="col-12 col-md-7">
                            <div class="card">
                                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center">
                                    @if ($activePermissions)
                                        <div>
                                            <h6 class="mb-0">{{ $activeModule }}</h6>
                                            <small class="text-muted">
                                                {{ $activeGroup }} — {{ $activeGranted }} of {{ count($activePermissions) }} granted
                                            </small>
                                        </div>
                                        <x-adminlte-button label="{{ $activeToggle['label'] }}"
                                            theme="{{ $activeToggle['theme'] }}"
                                            icon="{{ $activeToggle['icon'] }}"
                                            wire:click="toggleModule" wire:loading.attr="disabled" class="btn-sm" />
                                    @else
                                        <h6 class="mb-0 text-muted">Module Actions</h6>
                                    @endif
                                </div>

                                <div class="card-body p-3">
                                    @if ($activePermissions)
                                        <div class="overflow-auto" style="max-height: 22rem;">
                                            <div class="list-group list-group-flush">
                                                @foreach ($activePermissions as $permission)
                                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                        wire:key="action-{{ $permission['key'] }}">
                                                        <label class="mb-0" for="permission-{{ $permission['key'] }}">
                                                            {{ $permission['label'] }}
                                                            @if ($permission['note'])
                                                                <small class="d-block text-muted">{{ $permission['note'] }}</small>
                                                            @endif
                                                        </label>
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="permission-{{ $permission['key'] }}"
                                                                value="{{ $permission['key'] }}"
                                                                wire:model="selectedPermissions">
                                                            <label class="custom-control-label"
                                                                for="permission-{{ $permission['key'] }}">
                                                                <span class="sr-only">Grant {{ $permission['label'] }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center text-center text-muted py-5">
                                            <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                                            Pick a module from the list to switch its actions on or off.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <x-slot name="footerSlot">
            <span class="text-muted small mr-auto">
                {{ $isEditing ? 'Changes apply to every user holding this role.' : 'The role can be staffed as soon as it is created.' }}
            </span>
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Role' : 'Create Role' }}"
                theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
