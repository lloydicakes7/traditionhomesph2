<div wire:ignore.self>
    <x-adminlte-modal id="complaintRespondModal"
        title="Respond to Complaint"
        icon="fas fa-fw fa-reply" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($complaint)
                <div class="font-weight-bold mb-0">{{ $complaint->subject }}</div>
                <div class="text-muted small">
                    {{ $complaint->category_label }} · {{ $complaint->submitter_label }}
                </div>
                <div class="text-muted small mb-2">
                    Submitted {{ $complaint->created_at ? $complaint->created_at->format('M j, Y g:i A') : '—' }}
                </div>

                <div class="alert alert-light border small mb-3">
                    {{ $complaint->description }}
                </div>

                <div class="row">
                    <div class="col-12">
                        <x-adminlte-select name="responseStatus" label="Status" wire:model.defer="responseStatus"
                            igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-flag"></i></div>
                            </x-slot>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>

                    <div class="col-12">
                        <x-adminlte-textarea name="response" label="Response" rows="5"
                            placeholder="What the office found or did — the submitter reads this on their own complaints page."
                            wire:model.defer="response" igroup-size="sm" />
                    </div>
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Response" theme="secondary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
