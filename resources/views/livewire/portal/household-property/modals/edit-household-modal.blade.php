<div wire:ignore.self>
    <x-adminlte-modal id="editHouseholdModal" title="Edit Household Details"
        icon="fas fa-fw fa-house-user" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading household details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.type" label="Tenure Type" wire:model.defer="form.type" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                            @foreach ($householdTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                    <div class="col-12">
                        <x-adminlte-textarea name="form.prev_address" label="Previous Address"
                            wire:model.defer="form.prev_address" igroup-size="sm" rows="2"
                            placeholder="Optional — where the household lived before this unit">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-marked-alt"></i></div></x-slot>
                        </x-adminlte-textarea>
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Changes" theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
