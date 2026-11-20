<div>
    {{-- =================================================================== --}}
    {{-- HEADER                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-md-8 mb-2 mb-md-0">
                <h5 class="mb-0 font-weight-bold">Complaints</h5>
                <small class="text-muted">
                    What you have raised with the association, and the office's response to each one.
                </small>
            </div>

            <div class="col-12 col-md-4 text-right">
                @if ($canFile)
                    <x-adminlte-button label="File a Complaint" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#complaintRequestModal" class="btn-sm" />
                @endif
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- THE HOMEOWNER'S OWN COMPLAINTS                                      --}}
    {{-- =================================================================== --}}
    <div class="row">
        @forelse ($complaints as $complaint)
            <div class="col-12 col-md-6 col-lg-4 mb-3" wire:key="complaint-{{ $complaint->id }}">
                <x-portal.record-card :title="$complaint->subject"
                    :meta="'Submitted ' . ($complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : 'Not recorded')"
                    :status-theme="$complaint->status_badge_theme"
                    :status-label="$complaint->status_label"
                    :details="['Category' => $complaint->category_label]">
                    <div class="small mt-2 text-break" style="white-space: pre-line;">{{ $complaint->description }}</div>

                    @if ($complaint->other_description)
                        <div class="small text-muted mt-2 text-break">
                            <span class="font-weight-bold">Specified Complaint:</span>
                            {{ $complaint->other_description }}
                        </div>
                    @endif

                    @if ($complaint->is_responded)
                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="font-weight-bold small mb-1">
                                <i class="fas fa-fw fa-reply mr-1"></i>Response from
                                @if ($complaint->respondedByAdmin)
                                    {{ $complaint->respondedByAdmin->name }}
                                @endif
                            </div>
                            <p class="mb-0 small text-break">{{ $complaint->response }}</p>
                            @if ($complaint->responded_at)
                                <div class="text-muted small mt-1">
                                    {{ $complaint->responded_at->format('M j, Y g:i A') }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="text-muted small mt-3">
                        <i class="far fa-fw fa-clock mr-1"></i>{{ $complaint->status?->description() }}
                    </div>

                    <x-slot name="actions">
                        <x-adminlte-button theme="secondary" icon="fas fa-fw fa-eye"
                            title="View complaint" aria-label="View complaint"
                            data-toggle="modal" data-target="#complaintViewModal"
                            data-id="{{ $complaint->id }}" class="btn-sm" />

                        @can('updateOwn', $complaint)
                            <x-adminlte-button theme="primary" icon="fas fa-fw fa-edit"
                                title="Edit complaint" aria-label="Edit complaint"
                                data-toggle="modal" data-target="#complaintEditModal"
                                data-id="{{ $complaint->id }}" class="btn-sm" />
                        @endcan

                        @can('deleteOwn', $complaint)
                            <x-adminlte-button theme="danger" icon="fas fa-fw fa-trash"
                                title="Delete complaint" aria-label="Delete complaint"
                                data-toggle="modal" data-target="#complaintDeleteModal"
                                data-id="{{ $complaint->id }}" class="btn-sm" />
                        @endcan
                    </x-slot>
                </x-portal.record-card>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <x-adminlte-empty-state icon="fas fa-fw fa-question-circle"
                            title="You have not filed a complaint yet" />
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
