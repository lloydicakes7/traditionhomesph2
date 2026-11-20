<div wire:ignore.self>
    <x-adminlte-modal id="complaintEditModal" title="Edit Complaint"
        icon="fas fa-fw fa-pen" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($complaint)
                <form>
                    <div class="row">
                        <div class="col-12">
                            <x-adminlte-select name="form.category" label="Category"
                                wire:model="form.category" igroup-size="sm" required>
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                                </x-slot>
                                <option value="">Select a category...</option>
                                @foreach ($categories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>

                        @if (($form['category'] ?? '') === \App\Enums\ComplaintCategory::OTHER)
                            <div class="col-12">
                                <x-adminlte-input name="form.other_description" label="Specify Complaint"
                                    maxlength="255" placeholder="Describe the complaint type"
                                    wire:model.defer="form.other_description" igroup-size="sm">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-fw fa-pen"></i></div>
                                    </x-slot>
                                </x-adminlte-input>
                            </div>
                        @endif

                        <div class="col-12">
                            <x-adminlte-input name="form.subject" label="Subject"
                                placeholder="One line saying what this is about"
                                wire:model.defer="form.subject" igroup-size="sm" required>
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

                    <div class="alert alert-light border small mb-0 mt-3">
                        <i class="fas fa-fw fa-info-circle mr-1"></i>
                        Attachments already filed with this complaint are kept.
                    </div>
                </form>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Changes" theme="primary" icon="fas fa-fw fa-check"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-primary small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
