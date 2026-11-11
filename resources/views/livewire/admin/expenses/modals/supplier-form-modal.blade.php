{{-- resources/views/livewire/admin/expenses/modals/supplier-form-modal.blade.php --}}
<div wire:ignore.self>
    <x-adminlte-modal id="supplierFormModal" title="{{ $editing ? 'Edit Supplier' : 'Add Supplier' }}"
        icon="fas fa-fw fa-truck" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading supplier form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <x-adminlte-input name="form.name" label="Supplier Name"
                placeholder="e.g. ABC Hardware, Metro Security Agency"
                wire:model.defer="form.name" igroup-size="sm" />

            <x-adminlte-textarea name="form.description" label="Description" rows="2"
                placeholder="Contact person, phone, notes — anything the office needs on hand"
                wire:model.defer="form.description" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Supplier" theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
