<div wire:ignore.self>
    <x-adminlte-modal id="complaintViewModal" title="Complaint Details"
        icon="fas fa-fw fa-question-circle" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($complaint)
                {{-- =============================================================== --}}
                {{-- HEADER: what it is, and where it stands                          --}}
                {{-- =============================================================== --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-0 font-weight-bold">{{ $complaint->subject }}</h6>
                        <small class="text-muted">{{ $complaint->category_label }}</small>
                    </div>
                    <span class="badge badge-{{ $complaint->status_badge_theme }}">
                        {{ $complaint->status_label }}
                    </span>
                </div>

                {{-- =============================================================== --}}
                {{-- WHO FILED IT, AND WHEN                                           --}}
                {{-- =============================================================== --}}
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="text-muted small">Submitted By</div>
                        <div class="font-weight-medium">{{ $complaint->submitter_label }}</div>
                        <div class="text-muted small">
                            {{ $complaint->submitted_by_homeowner ? 'From the homeowner portal' : 'Filed by the office' }}
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="text-muted small">Submitted On</div>
                        <div class="font-weight-medium">
                            {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : '—' }}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Response</div>
                        <div class="font-weight-medium">
                            {{ $complaint->is_responded ? 'Answered' : 'No response yet' }}
                        </div>
                    </div>
                </div>

                {{-- =============================================================== --}}
                {{-- WHAT IT SAYS                                                     --}}
                {{-- =============================================================== --}}
                <div class="mt-3">
                    <div class="text-muted small mb-1">Description</div>
                    <p class="mb-0">{{ $complaint->description }}</p>
                </div>

                {{-- =============================================================== --}}
                {{-- THE OFFICE'S ANSWER, ONCE ONE HAS BEEN WRITTEN                    --}}
                {{-- =============================================================== --}}
                @if ($complaint->is_responded)
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-reply mr-1"></i>Office Response
                            @if ($complaint->respondedByAdmin)
                                — {{ $complaint->respondedByAdmin->name }},
                                {{ $complaint->responded_at->format('M j, Y g:i A') }}
                            @endif
                        </div>
                        <p class="mb-0 small">{{ $complaint->response }}</p>
                    </div>
                @endif

                {{-- =============================================================== --}}
                {{-- THE RECORD'S OWN TIMELINE                                        --}}
                {{-- =============================================================== --}}
                <div class="mt-3">
                    <div class="text-muted small mb-2">History</div>
                    @foreach ($complaint->timeline() as $step)
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
        </x-slot>
    </x-adminlte-modal>
</div>
