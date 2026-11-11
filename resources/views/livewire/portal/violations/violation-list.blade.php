<div>
    {{-- =================================================================== --}}
    {{-- HEADER                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12">
                <h5 class="mb-0 font-weight-bold">Violations</h5>
                <small class="text-muted">
                    Notices the association has issued against your household. Acknowledging a notice only
                    confirms that you received and read it.
                </small>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- CURRENT VIOLATIONS                                                  --}}
    {{-- =================================================================== --}}
    <div class="mb-2 px-1">
        <h6 class="font-weight-bold mb-0">
            <i class="fas fa-fw fa-exclamation-circle text-danger mr-1"></i>
            Current Violations
            <span class="badge badge-danger ml-1">{{ $violations['active']->count() }}</span>
        </h6>
    </div>

    @forelse ($violations['active'] as $violation)
        <div class="card shadow-sm mb-3" wire:key="active-violation-{{ $violation->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-0 font-weight-bold">{{ $violation->type_label }}</h6>
                        <small class="text-muted">
                            <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $violation->occurred_display }}
                        </small>
                    </div>
                    <span class="badge badge-{{ $violation->status_badge_theme }}">
                        {{ $violation->status_label }}
                    </span>
                </div>

                <p class="text-muted small mb-2">{{ $violation->status?->description() }}</p>

                <div class="row">
                    <div class="col-12 col-md-6 mb-1">
                        <div class="text-muted small">Location</div>
                        <div class="font-weight-medium">{{ $violation->location }}</div>
                    </div>
                    <div class="col-12 col-md-6 mb-1">
                        <div class="text-muted small">Acknowledgment</div>
                        <div class="font-weight-medium">
                            @if ($violation->is_acknowledged)
                                <i class="fas fa-fw fa-check-circle text-success mr-1"></i>Acknowledged —
                                {{ $violation->acknowledged_at->format('M j, Y g:i A') }}
                            @else
                                <i class="far fa-fw fa-circle text-muted mr-1"></i>Not yet acknowledged
                            @endif
                        </div>
                    </div>
                </div>

                @if ($violation->description)
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-fw fa-align-left mr-1"></i>{{ \Illuminate\Support\Str::limit($violation->description, 160) }}
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-3 flex-wrap" style="gap:0.5rem;">
                    <x-adminlte-button label="View Notice" theme="secondary" icon="fas fa-fw fa-eye"
                        data-toggle="modal" data-target="#violationViewModal" data-id="{{ $violation->id }}"
                        class="btn-sm" />

                    @can('acknowledgeOwn', $violation)
                        <x-adminlte-button label="Acknowledge Receipt" theme="primary" icon="fas fa-fw fa-check"
                            wire:click="acknowledge({{ $violation->id }})" wire:loading.attr="disabled" class="btn-sm" />
                    @endcan
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <x-adminlte-empty-state icon="fas fa-fw fa-check-circle"
                    title="No current violations" />
            </div>
        </div>
    @endforelse

    {{-- =================================================================== --}}
    {{-- VIOLATION HISTORY                                                   --}}
    {{-- =================================================================== --}}
    <div class="mt-4 mb-2 px-1">
        <h6 class="font-weight-bold mb-0">
            <i class="fas fa-fw fa-history text-muted mr-1"></i>
            Violation History
            <span class="badge badge-secondary ml-1">{{ $violations['history']->count() }}</span>
        </h6>
    </div>

    @forelse ($violations['history'] as $violation)
        <div class="card shadow-sm mb-3" wire:key="history-violation-{{ $violation->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div>
                        <h6 class="mb-0 font-weight-bold">{{ $violation->type_label }}</h6>
                        <small class="text-muted">
                            <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $violation->occurred_display }}
                        </small>
                    </div>
                    <span class="badge badge-{{ $violation->history_badge_theme }}">
                        {{ $violation->status_label }}
                    </span>
                </div>

                <div class="small text-muted">
                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>{{ $violation->location }}
                    @if ($violation->resolved_at)
                        · Resolved {{ $violation->resolved_at->format('M j, Y') }}
                    @endif
                </div>

                <div class="text-right mt-2">
                    <x-adminlte-button label="View Notice" theme="secondary" icon="fas fa-fw fa-eye"
                        data-toggle="modal" data-target="#violationViewModal" data-id="{{ $violation->id }}"
                        class="btn-sm" />
                </div>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body">
                <x-adminlte-empty-state icon="fas fa-fw fa-history"
                    title="No violation history" />
            </div>
        </div>
    @endforelse
</div>
