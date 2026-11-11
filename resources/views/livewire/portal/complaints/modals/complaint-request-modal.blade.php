<div wire:ignore.self>
    <x-adminlte-modal id="complaintRequestModal" title="File a Complaint"
        icon="fas fa-fw fa-question-circle" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Preparing the complaint form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form>
            {{-- =========================================================== --}}
            {{-- WHAT IT IS ABOUT                                            --}}
            {{-- =========================================================== --}}
            <div class="row">
                <div class="col-12">
                    <x-adminlte-select name="form.category" label="Category"
                        wire:model.defer="form.category" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                        </x-slot>
                        <option value="">Select a category...</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>

                <div class="col-12">
                    <x-adminlte-input name="form.subject" label="Subject"
                        placeholder="One line saying what this is about"
                        wire:model.defer="form.subject" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-heading"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                <div class="col-12">
                    <x-adminlte-textarea name="form.description" label="Description" rows="5"
                        placeholder="What happened — when and where, what you saw, and anything else the office needs to look into it."
                        wire:model.defer="form.description" igroup-size="sm" />
                </div>
            </div>

            <div class="alert alert-info small mb-0">
                <i class="fas fa-fw fa-info-circle mr-1"></i>
                Your complaint is filed under your own household record. The office reviews it and posts a
                response on this page, so you can follow it here.
            </div>
        </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Submit Complaint" theme="primary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Submitting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
