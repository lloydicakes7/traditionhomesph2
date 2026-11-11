<div wire:ignore.self>
    <x-adminlte-modal id="violationViewModal" title="Violation Details"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the violation…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($violation)
                {{-- =============================================================== --}}
                {{-- HEADER: who, what, and where it stands                           --}}
                {{-- =============================================================== --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-0 font-weight-bold">{{ $violation->resident_label }}</h6>
                        <small class="text-muted">{{ $violation->type_label }}</small>
                    </div>
                    <span class="badge badge-{{ $violation->status_badge_theme }}">
                        {{ $violation->status_label }}
                    </span>
                </div>

                {{-- =============================================================== --}}
                {{-- THE FACTS                                                        --}}
                {{-- =============================================================== --}}
                <div class="row">
                    <div class="col-6 col-md-4 mb-2">
                        <div class="text-muted small">Date &amp; Time</div>
                        <div class="font-weight-medium">{{ $violation->occurred_display }}</div>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <div class="text-muted small">Location</div>
                        <div class="font-weight-medium">{{ $violation->location }}</div>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <div class="text-muted small">Issued By</div>
                        <div class="font-weight-medium">{{ $violation->issuedByAdmin->name ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <div class="text-muted small">Issued On</div>
                        <div class="font-weight-medium">
                            {{ $violation->issued_at ? $violation->issued_at->format('M j, Y g:i A') : 'Not issued' }}
                        </div>
                    </div>
                    <div class="col-6 col-md-4 mb-2">
                        <div class="text-muted small">Acknowledgment</div>
                        <div class="font-weight-medium">
                            @if ($violation->is_acknowledged)
                                <i class="fas fa-fw fa-check-circle text-success mr-1"></i>
                            @else
                                <i class="far fa-fw fa-circle text-muted mr-1"></i>
                            @endif
                            {{ $violation->acknowledged_display }}
                        </div>
                    </div>
                </div>

                {{-- =============================================================== --}}
                {{-- WHAT HAPPENED                                                    --}}
                {{-- =============================================================== --}}
                <div class="mt-3">
                    <div class="text-muted small mb-1">Description</div>
                    <p class="mb-0">{{ $violation->description }}</p>
                </div>

                {{-- =============================================================== --}}
                {{-- THE OFFICE'S OWN REPORT, WHEN ONE WAS FILED                       --}}
                {{-- =============================================================== --}}
                @if ($violation->has_office_report)
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-file-alt mr-1"></i>Report to the HOA Office
                        </div>
                        <p class="mb-0 small">{{ $violation->office_report }}</p>
                    </div>
                @endif

                {{-- =============================================================== --}}
                {{-- HOW IT WAS SETTLED, ONCE IT HAS BEEN                              --}}
                {{-- =============================================================== --}}
                @if ($violation->resolved_at)
                    <div class="alert alert-success border mt-3 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-check-circle mr-1"></i>Resolved
                            {{ $violation->resolved_at->format('M j, Y g:i A') }}
                            @if ($violation->resolvedByAdmin)
                                by {{ $violation->resolvedByAdmin->name }}
                            @endif
                        </div>
                        @if ($violation->resolution_notes)
                            <p class="mb-0 small">{{ $violation->resolution_notes }}</p>
                        @endif
                    </div>
                @endif

                {{-- =============================================================== --}}
                {{-- THE RECORD'S OWN TIMELINE                                        --}}
                {{-- =============================================================== --}}
                <div class="mt-3">
                    <div class="text-muted small mb-2">History</div>
                    @foreach ($violation->timeline() as $step)
                        <div class="d-flex align-items-start mb-2">
                            <i class="{{ $step['icon'] }} mr-2 mt-1"></i>
                            <div>
                                <div class="font-weight-medium">{{ $step['label'] }}</div>
                                <div class="text-muted small">
                                    {{ $step['when'] }}@if ($step['note']) · {{ $step['note'] }}@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            @if ($violation)
                <a href="{{ route('support.violations.notice', $violation->id) }}" target="_blank"
                    class="btn btn-sm btn-primary">
                    <i class="fas fa-fw fa-print mr-1"></i> Print Notice
                </a>
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
