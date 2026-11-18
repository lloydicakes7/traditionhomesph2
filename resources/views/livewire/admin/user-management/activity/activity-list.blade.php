<div id="activityLogSection">
    {{-- =================================================================== --}}
    {{-- SEARCH + DATE/TIME WINDOW                                           --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-md-6 mb-2 mb-lg-0">
                <x-adminlte-input name="search" label="Search"
                    placeholder="Search admin, action, module, description..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-2 mb-2 mb-lg-0">
                <x-adminlte-input name="date" type="date" label="Date"
                    wire:model="date" igroup-size="sm" fgroup-class="mb-0" />
            </div>

            <div class="col-6 col-md-2 mb-2 mb-lg-0">
                <x-adminlte-input name="fromTime" type="time" label="From"
                    wire:model="fromTime" igroup-size="sm" fgroup-class="mb-0" />
            </div>

            <div class="col-6 col-md-2 mb-2 mb-lg-0">
                <x-adminlte-input name="toTime" type="time" label="To"
                    wire:model="toTime" igroup-size="sm" fgroup-class="mb-0" />
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
            @can('delete', \App\Models\AdminActivity::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#activityMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE (4 columns)                                           --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            {{-- Column 1: select-all + Date/Time --}}
                            <th style="width:22%;" class="align-middle">
                                <div class="d-flex align-items-center">
                                    @can('delete', \App\Models\AdminActivity::class)
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input type="checkbox" class="custom-control-input" id="selectAll"
                                                wire:model="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    @endcan
                                    <span>Date/Time</span>
                                </div>
                            </th>
                            <th style="width:20%;" class="align-middle">Admin</th>
                            <th class="align-middle">Activity</th>
                            <th style="width:80px;" class="text-right align-middle">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr wire:key="activity-row-{{ $activity->id }}">
                                <td class="align-middle small text-nowrap">
                                    <div class="d-flex align-items-center">
                                        @can('delete', \App\Models\AdminActivity::class)
                                            <div class="custom-control custom-checkbox mr-2">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="act-{{ $activity->id }}" value="{{ $activity->id }}"
                                                    wire:model="selectedIds">
                                                <label class="custom-control-label" for="act-{{ $activity->id }}"></label>
                                            </div>
                                        @endcan
                                        <span>{{ $activity->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="align-middle small text-break">
                                    {{ $activity->admin->name ?? 'Not recorded' }}
                                </td>
                                <td class="align-middle">
                                    <div class="mb-1">
                                        <span class="badge badge-{{ $activity->action_theme }}">
                                            {{ $activity->action_label }}
                                        </span>
                                        <span class="badge badge-secondary">{{ ucfirst($activity->module) }}</span>
                                    </div>
                                    <div class="small text-break">{{ $activity->description }}</div>
                                </td>
                                <td class="text-right align-middle text-nowrap">
                                    @can('delete', \App\Models\AdminActivity::class)
                                        <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                            data-toggle="modal" data-target="#activityDeleteModal"
                                            data-id="{{ $activity->id }}" class="btn-xs" title="Delete" />
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-fw fa-history fa-2x mb-2"></i>
                                    <p class="mb-0">No activities match these filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS (hidden on large screens)                              --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        {{-- Select all (mobile) --}}
        @can('delete', \App\Models\AdminActivity::class)
            @if ($activities->count())
                <div class="custom-control custom-checkbox mb-2 px-1 pl-4">
                    <input type="checkbox" class="custom-control-input" id="selectAllMobile"
                        wire:model="selectAll">
                    <label class="custom-control-label small font-weight-bold text-muted"
                        for="selectAllMobile">Select all</label>
                </div>
            @endif
        @endcan

        @forelse ($activities as $activity)
            <div class="card mb-3 shadow-sm" wire:key="activity-card-{{ $activity->id }}">
                <div class="card-body py-3">
                    {{-- Header: checkbox + date + delete --}}
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            @can('delete', \App\Models\AdminActivity::class)
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input"
                                        id="act-m-{{ $activity->id }}" value="{{ $activity->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="act-m-{{ $activity->id }}"></label>
                                </div>
                            @endcan
                            <span class="text-muted small">
                                <i class="far fa-fw fa-clock"></i>
                                {{ $activity->created_at->format('M d, Y h:i A') }}
                            </span>
                        </div>
                        @can('delete', \App\Models\AdminActivity::class)
                            <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                data-toggle="modal" data-target="#activityDeleteModal"
                                data-id="{{ $activity->id }}" class="btn-xs ml-2" title="Delete" />
                        @endcan
                    </div>

                    {{-- Admin --}}
                    <div class="font-weight-bold mt-2">
                        <i class="fas fa-fw fa-user text-muted"></i>
                        {{ $activity->admin->name ?? 'Not recorded' }}
                    </div>

                    {{-- Badges --}}
                    <div class="mt-1">
                        <span class="badge badge-{{ $activity->action_theme }}">
                            {{ $activity->action_label }}
                        </span>
                        <span class="badge badge-secondary">{{ ucfirst($activity->module) }}</span>
                    </div>

                    {{-- Description --}}
                    <div class="small text-break mt-2">{{ $activity->description }}</div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-history fa-2x mb-2"></i>
                    <p class="mb-0">No activities match these filters.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination (rendered once for both desktop & mobile) --}}
    @if ($activities->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $activities->links() }}
        </div>
    @endif
</div>
