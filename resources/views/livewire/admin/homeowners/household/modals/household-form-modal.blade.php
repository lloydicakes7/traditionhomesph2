<div wire:ignore.self>
    <x-adminlte-modal id="householdFormModal"
        title="{{ $isEditing ? 'Edit Household' : 'Add New Household' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading household form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- ===== Head of the Family ===== --}}
                <div>
                    <x-adminlte-select2 id="householdFormHead" name="form.head_resident_id" label="Resident"
                        wire:model.defer="form.head_resident_id" igroup-size="sm"
                        :config="['placeholder' => 'Select Resident']">
                        <option value="">Select Resident</option>
                        @foreach ($residents as $resident)
                            <option value="{{ $resident->id }}">{{ $resident->full_name }}</option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-select class="text-uppercase" name="form.type" label="Tenure Type" wire:model.defer="form.type" igroup-size="sm">
                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                        @foreach ($householdTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-input name="form.relationship" label="Relationship"
                        placeholder="e.g., Son, Mother, Uncle" data-thha-uppercase
                        wire:model.defer="form.relationship" igroup-size="sm">
                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-link"></i></div></x-slot>
                    </x-adminlte-input>

                    {{-- ===== Household ===== --}}
                    <x-adminlte-select2 id="householdFormPropertyId" name="form.property_id" label="Property"
                        wire:model.defer="form.property_id" igroup-size="sm"
                        :config="['placeholder' => 'Select Property']">
                        <option value="">Select Property</option>
                        @foreach ($properties as $property)
                            <option value="{{ $property->id }}">
                                {{ $property->block_lot_label }}@if ($property->address), {{ $property->address->subdivision }}@endif
                            </option>
                        @endforeach
                    </x-adminlte-select2>

                    <x-adminlte-textarea name="form.prev_address" label="Previous Address"
                        data-thha-uppercase wire:model.defer="form.prev_address" igroup-size="sm" rows="2"
                        placeholder="Optional. Previous address before moving to this property">
                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-marked-alt"></i></div></x-slot>
                    </x-adminlte-textarea>
                </div>

            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Household' : 'Create Household' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
