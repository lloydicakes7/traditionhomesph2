<div wire:ignore.self>
    <x-adminlte-modal id="userViewModal" title="Admin Details"
        icon="fas fa-fw fa-user" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the account…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        @if ($viewingAdmin)
            {{-- ========================================================= --}}
            {{-- THE TWO TABS                                               --}}
            {{-- ========================================================= --}}
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeViewTab === 'overview' ? 'active' : '' }}"
                        href="#" wire:click.prevent="switchViewTab('overview')" role="tab">
                        <i class="fas fa-fw fa-info-circle"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeViewTab === 'activities' ? 'active' : '' }}"
                        href="#" wire:click.prevent="switchViewTab('activities')" role="tab">
                        <i class="fas fa-fw fa-history"></i> Activities
                    </a>
                </li>
            </ul>

            <div class="tab-content p-3">
                {{-- ===================================================== --}}
                {{-- WHO THE ACCOUNT IS                                     --}}
                {{-- ===================================================== --}}
                <div class="tab-pane {{ $activeViewTab === 'overview' ? 'active' : '' }}" role="tabpanel">
                    <div class="text-center mt-2">
                        <img src="{{ $viewingAdmin->adminlte_image() }}" class="img-circle elevation-2 mb-3"
                            style="width:96px; height:96px; object-fit:cover;" alt="{{ $viewingAdmin->name }}">
                        <h5 class="font-weight-bold mb-1">{{ $viewingAdmin->name }}</h5>
                        <p class="text-muted mb-2">{{ $viewingAdmin->email }}</p>
                        <p class="mb-2">
                            <span class="badge badge-{{ $viewingAdmin->role->badge_theme ?? 'secondary' }}">
                                {{ $viewingAdmin->role->name ?? '—' }}
                            </span>
                            <span class="badge badge-{{ $viewingAdmin->is_active ? 'success' : 'warning' }} ml-1">
                                {{ $viewingAdmin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                        <hr>
                        <div class="text-left small text-muted">
                            <p class="mb-1"><strong>Account created:</strong> {{ $viewingAdmin->created_at->format('M d, Y h:i A') }}</p>
                            <p class="mb-1"><strong>Last updated:</strong> {{ $viewingAdmin->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="mb-0">
                                <strong>Email verified:</strong>
                                {{ $viewingAdmin->email_verified_at ? $viewingAdmin->email_verified_at->format('M d, Y h:i A') : 'Not verified' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ===================================================== --}}
                {{-- WHAT THEY HAVE DONE                                    --}}
                {{-- ===================================================== --}}
                <div class="tab-pane {{ $activeViewTab === 'activities' ? 'active' : '' }}" role="tabpanel">
                    @if ($viewingActivities && $viewingActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Action</th>
                                        <th>Module</th>
                                        <th>Description</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewingActivities as $activity)
                                        <tr>
                                            <td class="text-nowrap small">{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $activity->action_theme }}">
                                                    {{ $activity->action_label }}
                                                </span>
                                            </td>
                                            <td class="small">{{ ucfirst($activity->module) }}</td>
                                            <td class="small">{{ $activity->description }}</td>
                                            <td class="text-right">
                                                {{-- A Bootstrap trigger: the delete dialog is
                                                     mounted by the page and loads the log
                                                     itself, so nothing round-trips until the
                                                     officer confirms. --}}
                                                <button type="button" class="btn btn-xs btn-danger"
                                                    data-toggle="modal" data-target="#activityDeleteModal"
                                                    data-id="{{ $activity->id }}"
                                                    title="Delete this activity log">
                                                    <i class="fas fa-fw fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $viewingActivities->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-fw fa-history fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No activities recorded for this admin.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
