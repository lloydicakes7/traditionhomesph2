<div wire:ignore.self>
    <x-adminlte-modal id="expenseCategoryDeleteModal"
        title="Delete Category"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading category…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Delete category <strong>{{ $deletingCategoryLabel }}</strong>?</p>

            <p class="text-danger small mt-2 mb-0">
                <i class="fas fa-fw fa-info-circle"></i> This action cannot be undone. A category that expenses already use cannot be deleted.
            </p>

            @error('delete')
                <p class="text-danger small mt-2 mb-0">{{ $message }}</p>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
