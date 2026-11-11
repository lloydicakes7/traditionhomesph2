<div wire:ignore.self>
    <x-adminlte-modal id="expenseCategoryFormModal"
        title="{{ $editing ? 'Edit Category' : 'Add Category' }}"
        icon="fas fa-fw fa-tags" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading category form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <x-adminlte-input name="form.name" label="Category Name"
                placeholder="e.g. Security, Utilities, Repairs"
                wire:model.defer="form.name" igroup-size="sm" />

            <x-adminlte-textarea name="form.description" label="Description" rows="2"
                placeholder="What belongs under this category"
                wire:model.defer="form.description" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Category" theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
