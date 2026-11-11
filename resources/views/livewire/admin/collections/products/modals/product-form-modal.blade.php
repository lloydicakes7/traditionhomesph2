<div wire:ignore.self>
    <x-adminlte-modal id="productFormModal"
        title="{{ $productId ? 'Edit Product or Service' : 'Add Product or Service' }}"
        icon="fas fa-fw fa-tags" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading product form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                <div class="col-12 col-sm-8">
                    <x-adminlte-input name="form.name" label="Name"
                        placeholder="e.g., Vehicle Sticker"
                        wire:model.defer="form.name" igroup-size="sm" />
                </div>
                <div class="col-12 col-sm-4">
                    <x-adminlte-select name="form.type" label="Type" wire:model="form.type" igroup-size="sm">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-select name="form.product_category_id" label="Category"
                        wire:model="form.product_category_id" igroup-size="sm">
                        <option value="">Uncategorised</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="col-12 col-sm-3">
                    <x-adminlte-input name="form.price" label="Price" type="number" step="0.01" min="0"
                        wire:model.lazy="form.price" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-12 col-sm-3">
                    <x-adminlte-input name="form.unit" label="Unit"
                        placeholder="e.g., per sticker"
                        wire:model.defer="form.unit" igroup-size="sm" />
                </div>
            </div>

            <x-adminlte-textarea name="form.description" label="Description" rows="2"
                wire:model.defer="form.description" igroup-size="sm" />

            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="productIsActive"
                    wire:model="form.is_active">
                <label class="custom-control-label" for="productIsActive">
                    Available to bill
                </label>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $productId ? 'Save' : 'Add' }}" theme="primary"
                icon="fas fa-fw fa-save" wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
