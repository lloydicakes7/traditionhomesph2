<div id="activityLogSection">
    <x-adminlte-card>
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-sm-0">
                <h5 class="mb-0 font-weight-bold">Activity Log</h5>
                <small class="text-muted">What the office has done across the system.</small>
            </div>

            @can('delete', \App\Models\AdminActivity::class)
                <x-adminlte-button label="Delete All Logs" theme="danger" icon="fas fa-fw fa-trash-alt"
                    data-toggle="modal" data-target="#deleteAllActivitiesModal" class="btn-sm"
                    :disabled="$activities->total() === 0" />
            @endcan
        </div>
    </x-adminlte-card>

    <div class="card d-none d-lg-block">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th style="width:18%;" class="align-middle">Date/Time</th>
                        <th style="width:12%;" class="align-middle">Action</th>
                        <th style="width:15%;" class="align-middle">Module</th>
                        <th class="align-middle">Description</th>
                        <th style="width:70px;" class="text-right align-middle">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr wire:key="activity-row-{{ $activity->id }}">
                            <td class="align-middle text-nowrap small">
                                {{ $activity->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $activity->action_theme }}">
                                    {{ $activity->action_label }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border">{{ ucfirst($activity->module) }}</span>
                            </td>
                            <td class="align-middle small text-break">{{ $activity->description }}</td>
                            <td class="text-right align-middle">
                                @can('delete', \App\Models\AdminActivity::class)
                                    <button type="button" class="btn btn-xs btn-danger"
                                        data-toggle="modal" data-target="#activityDeleteModal"
                                        data-id="{{ $activity->id }}" title="Delete this activity log">
                                        <i class="fas fa-fw fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-history fa-2x mb-2"></i>
                                <p class="mb-0">No activities recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-block d-lg-none">
        @forelse ($activities as $activity)
            <div class="card mb-3 shadow-sm" wire:key="activity-card-{{ $activity->id }}">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="font-weight-bold">
                                <span class="badge badge-{{ $activity->action_theme }}">
                                    {{ $activity->action_label }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1">
                                {{ $activity->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <span class="badge badge-light border text-nowrap">
                            {{ ucfirst($activity->module) }}
                        </span>
                    </div>

                    <p class="small text-break mb-3">{{ $activity->description }}</p>

                    @can('delete', \App\Models\AdminActivity::class)
                        <div class="text-right">
                            <button type="button" class="btn btn-xs btn-outline-danger"
                                data-toggle="modal" data-target="#activityDeleteModal"
                                data-id="{{ $activity->id }}" title="Delete this activity log">
                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-history fa-2x mb-2"></i>
                    <p class="mb-0">No activities recorded yet.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($activities->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $activities->links() }}
        </div>
    @endif
</div>
