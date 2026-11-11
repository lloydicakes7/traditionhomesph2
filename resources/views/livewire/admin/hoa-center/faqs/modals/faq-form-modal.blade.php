<div wire:ignore.self>
    <x-adminlte-modal id="faqFormModal"
        title="{{ $isEditing ? 'Edit FAQ' : 'New FAQ' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading FAQ form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <x-adminlte-input name="form.question" label="Question" maxlength="255"
                    placeholder="e.g., How do I get a homeowner portal account?"
                    wire:model.defer="form.question" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-question"></i></div>
                    </x-slot>
                </x-adminlte-input>

                <x-adminlte-textarea name="form.answer" label="Answer" rows="6" maxlength="5000"
                    placeholder="Write the answer. Blank lines start a new paragraph on the home page."
                    wire:model.defer="form.answer" igroup-size="sm" />
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update FAQ' : 'Add FAQ' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
