<div wire:ignore.self>
    <x-adminlte-modal id="complaintViewModal" title="Complaint Details"
        icon="fas fa-fw fa-question-circle" theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($complaint)
                <div class="d-flex justify-content-between align-items-start mb-3" style="gap:0.75rem;">
                    <div class="min-width-0">
                        <h6 class="mb-0 font-weight-bold text-break">{{ $complaint->subject }}</h6>
                        <small class="text-muted">{{ $complaint->category_label }}</small>
                    </div>
                    <span class="badge badge-{{ $complaint->status_badge_theme }} flex-shrink-0">
                        {{ $complaint->status_label }}
                    </span>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-2">
                        <div class="text-muted small">Submitted On</div>
                        <div class="font-weight-medium">
                            {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : 'Not recorded' }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <div class="text-muted small">Response</div>
                        <div class="font-weight-medium">
                            {{ $complaint->is_responded ? 'Answered' : 'No response yet' }}
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <div class="text-muted small mb-1">Description</div>
                    <p class="mb-0 text-break" style="white-space: pre-line;">{{ $complaint->description }}</p>
                </div>

                @if ($complaint->other_description)
                    <div class="mt-3">
                        <div class="text-muted small mb-1">Specified Complaint</div>
                        <p class="mb-0 text-break">{{ $complaint->other_description }}</p>
                    </div>
                @endif

                @if ($complaint->attachments->isNotEmpty())
                    <div class="mt-3">
                        <div class="text-muted small mb-2">Attachments</div>
                        @foreach ($complaint->attachments as $attachment)
                            <div class="small text-break mb-1">
                                <i class="fas fa-fw fa-paperclip text-muted mr-1"></i>
                                {{ $attachment->original_name }}
                                <span class="text-muted">({{ $attachment->size_label }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($complaint->is_responded)
                    <div class="alert alert-light border mt-3 mb-0">
                        <div class="font-weight-bold small mb-1">
                            <i class="fas fa-fw fa-reply mr-1"></i>Office Response
                            @if ($complaint->respondedByAdmin)
                               : {{ $complaint->respondedByAdmin->name }},
                                {{ $complaint->responded_at->format('M j, Y g:i A') }}
                            @endif
                        </div>
                        <p class="mb-0 small text-break">{{ $complaint->response }}</p>
                    </div>
                @endif

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
