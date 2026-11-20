<div>
    {{-- =================================================================== --}}
    {{-- HEADER                                                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12">
                <h5 class="mb-0 font-weight-bold">Violations</h5>
                <small class="text-muted">
                    Notices the association has issued against your household.
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

    <div class="row">
        @forelse ($violations['active'] as $violation)
            <div class="col-12 col-md-6 col-lg-4 mb-3" wire:key="active-violation-{{ $violation->id }}">
                <x-portal.record-card :title="$violation->type_label"
                    :meta="'Occurred ' . $violation->occurred_display"
                    :status-theme="$violation->status_badge_theme"
                    :status-label="$violation->status_label"
                    :details="[
                        'Location' => $violation->location,
                        'Issued By' => $violation->issuedByAdmin->name ?? 'The HOA Office',
                    ]">
                    @if ($violation->status?->description())
                        <p class="text-muted small mb-0 mt-2">{{ $violation->status?->description() }}</p>
                    @endif

                    @if ($violation->description)
                        <div class="small mt-2 text-break">
                            <i class="fas fa-fw fa-align-left mr-1 text-muted"></i>{{ \Illuminate\Support\Str::limit($violation->description, 160) }}
                        </div>
                    @endif

                    <x-slot name="actions">
                        <x-adminlte-button theme="secondary" icon="fas fa-fw fa-eye"
                            title="View notice" aria-label="View notice"
                            data-toggle="modal" data-target="#violationViewModal"
                            data-id="{{ $violation->id }}" class="btn-sm" />
                    </x-slot>
                </x-portal.record-card>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <x-adminlte-empty-state icon="fas fa-fw fa-check-circle"
                            title="No current violations" />
                    </div>
                </div>
            </div>
        @endforelse
    </div>

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

    <div class="row">
        @forelse ($violations['history'] as $violation)
            <div class="col-12 col-md-6 col-lg-4 mb-3" wire:key="history-violation-{{ $violation->id }}">
                <x-portal.record-card :title="$violation->type_label"
                    :meta="'Occurred ' . $violation->occurred_display"
                    :status-theme="$violation->history_badge_theme"
                    :status-label="$violation->status_label"
                    :details="[
                        'Location' => $violation->location,
                        'Resolved' => $violation->resolved_at ? $violation->resolved_at->format('M j, Y') : null,
                    ]">
                    <x-slot name="actions">
                        <x-adminlte-button theme="secondary" icon="fas fa-fw fa-eye"
                            title="View notice" aria-label="View notice"
                            data-toggle="modal" data-target="#violationViewModal"
                            data-id="{{ $violation->id }}" class="btn-sm" />
                    </x-slot>
                </x-portal.record-card>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <x-adminlte-empty-state icon="fas fa-fw fa-history"
                            title="No violation history" />
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
