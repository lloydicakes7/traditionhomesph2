<div wire:ignore.self>
    <x-adminlte-modal id="addHouseholdModal" title="Add Household"
        icon="fas fa-fw fa-house-user" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading household form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row">
                    <div class="col-6 col-md-3">
                        <x-adminlte-input name="form.block" type="number" min="1" label="BLK"
                            placeholder="BLK" wire:model.lazy="form.block" igroup-size="sm" />
                    </div>
                    <div class="col-6 col-md-3">
                        <x-adminlte-input name="form.lot" type="number" min="1" label="LOT"
                            placeholder="LOT" wire:model.lazy="form.lot" igroup-size="sm" />
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="small font-weight-bold mb-1">Associated Address</label>
                        <p class="form-control-plaintext border rounded bg-light px-2 py-1 mb-0 small">
                            <i class="fas fa-fw fa-map-marker-alt mr-1"></i>
                            {{ $unit ? $unit['address'] : ($associationAddress ? $associationAddress->full_address : '—') }}
                        </p>
                    </div>
                </div>

                @error('form.block')<span class="d-block text-danger small mt-1">{{ $message }}</span>@enderror
                @error('form.lot')<span class="d-block text-danger small mt-1">{{ $message }}</span>@enderror

                @if ($unit && $unit['is_occupied'])
                    <div class="alert alert-warning mt-3 mb-0">
                        Someone is already registered at this address. Ask the office to add you to that household.
                    </div>
                @endif

                <div class="row mt-2">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.type" label="Tenure Type" wire:model.defer="form.type" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div></x-slot>
                            @foreach ($householdTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.relationship" label="Relationship" wire:model.defer="form.relationship" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-link"></i></div></x-slot>
                            <option value="">— Select —</option>
                            @foreach ($relationshipOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                @error('form.type')<span class="d-block text-danger small mt-1">{{ $message }}</span>@enderror
                @error('form.relationship')<span class="d-block text-danger small mt-1">{{ $message }}</span>@enderror

                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.prev_address" label="Previous Address"
                            wire:model.defer="form.prev_address" igroup-size="sm" rows="2"
                            placeholder="Optional — where the household lived before this unit">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-marked-alt"></i></div></x-slot>
                        </x-adminlte-textarea>
                    </div>
                </div>
                @error('form.prev_address')<span class="d-block text-danger small mt-1">{{ $message }}</span>@enderror
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Create Household" theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
