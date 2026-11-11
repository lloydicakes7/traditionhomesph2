<div wire:ignore.self>
    <x-adminlte-modal id="propertyAssignHomeownerModal"
        title="Assign Homeowner"
        icon="fas fa-fw fa-user-plus" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading homeowner form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <x-adminlte-select2 id="propertyAssignResidentId" name="assignForm.head_resident_id" label="Resident"
                    wire:model.defer="assignForm.head_resident_id" igroup-size="sm"
                    :config="['placeholder' => 'Select a resident...']">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                    <option value="">Select a resident...</option>
                    @if (!$loaded)
                        <option value="" disabled>No residents available</option>
                    @elseif ($residents->isEmpty())
                        <option value="" disabled>No residents available to assign</option>
                    @else
                        @foreach ($residents as $resident)
                            <option value="{{ $resident->id }}">{{ $resident->full_name }}</option>
                        @endforeach
                    @endif
                </x-adminlte-select2>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="assignForm.type" label="Tenure Type" wire:model.defer="assignForm.type" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                            @foreach ($householdTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="assignForm.relationship" label="Relationship"
                            placeholder="e.g., Owner, Renter"
                            wire:model.defer="assignForm.relationship" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-link"></i></div></x-slot>
                        </x-adminlte-input>
                    </div>

                    <div class="col-12 col-md-6">
                    </div>
                </div>

                <x-adminlte-textarea name="assignForm.prev_address" label="Previous Address"
                    wire:model.defer="assignForm.prev_address" igroup-size="sm" rows="3">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-marked-alt"></i></div></x-slot>
                </x-adminlte-textarea>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Assign" theme="success" icon="fas fa-fw fa-save"
                wire:click="saveAssignHomeowner" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="saveAssignHomeowner" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
