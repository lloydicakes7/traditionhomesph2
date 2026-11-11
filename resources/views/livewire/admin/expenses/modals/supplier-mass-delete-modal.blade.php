{{-- resources/views/livewire/admin/expenses/modals/supplier-mass-delete-modal.blade.php --}}
<div wire:ignore.self>
    <x-adminlte-modal id="supplierMassDeleteModal"
        title="Delete Selected Suppliers"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading selection…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Are you sure you want to delete the <strong>{{ count($selectedIds) }}</strong> selected suppliers?</p>

            <p class="text-danger small mt-2 mb-0">
                <i class="fas fa-fw fa-info-circle"></i> A supplier that expenses already name cannot be deleted.
            </p>

            @error('massDelete')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="deleteMass" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="deleteMass" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
