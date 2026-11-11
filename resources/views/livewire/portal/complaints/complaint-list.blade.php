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
            <div class="col-12 col-md-6 mb-3">
                <div class="card shadow-sm mb-3" wire:key="complaint-{{ $complaint->id }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-0 font-weight-bold">{{ $complaint->subject }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-fw fa-tag mr-1"></i>{{ $complaint->category_label }}
                                    · Submitted
                                    {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : '—' }}
                                </small>
                            </div>
                            <span class="badge badge-{{ $complaint->status_badge_theme }}">
                                {{ $complaint->status_label }}
                            </span>
                        </div>

                        <div class="small">{{ $complaint->description }}</div>

                        @if ($complaint->is_responded)
                            <div class="alert alert-light border mt-3 mb-0">
                                <div class="font-weight-bold small mb-1">
                                    <i class="fas fa-fw fa-reply mr-1"></i>Response from
                                    @if ($complaint->respondedByAdmin)
                                        {{ $complaint->respondedByAdmin->name }}
                                    @endif
                                </div>
                                <p class="mb-0 small">{{ $complaint->response }}</p>
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
                    </div>
                </div>
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
