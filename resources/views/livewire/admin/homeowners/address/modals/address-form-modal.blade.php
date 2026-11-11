<div wire:ignore.self>
    <x-adminlte-modal id="addressFormModal"
        title="{{ $isEditing ? 'Edit Address' : 'New Address' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading address form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- Row 1: Label, Country --}}
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.label" label="Label"
                            placeholder="e.g., Main Office, Clubhouse, Gate 1"
                            wire:model.defer="form.label" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.country" label="Country"
                            placeholder="e.g., Philippines"
                            wire:model.defer="form.country" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-globe-asia"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                {{-- Row 2: Street, Subdivision --}}
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.street" label="Street"
                            placeholder="Main Road Street"
                            wire:model.defer="form.street" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-road"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.subdivision" label="Subdivision"
                            placeholder="e.g., Tradition Homes"
                            wire:model.defer="form.subdivision" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-home"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                {{-- PSGC geographic selects --}}
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.region" label="Region" wire:model="form.region"
                            igroup-size="sm" :disabled="empty($regions)">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-map-marker-alt"></i></div>
                            </x-slot>
                            <option value="">— Select Region —</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region['name'] }}">{{ $region['name'] }}</option>
                            @endforeach
                        </x-adminlte-select>
                        @if (empty($regions))
                            <small class="text-muted"><i class="fas fa-fw fa-sync-alt fa-spin"></i> Loading regions…</small>
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.province" label="Province" wire:model="form.province"
                            igroup-size="sm" :disabled="empty($provinces)">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-map"></i></div>
                            </x-slot>
                            <option value="">— Select Province —</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province['name'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.city" label="City / Municipality" wire:model="form.city"
                            igroup-size="sm" :disabled="empty($citiesMunicipalities)">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-city"></i></div>
                            </x-slot>
                            <option value="">— Select City —</option>
                            @foreach ($citiesMunicipalities as $city)
                                <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>

                    <div class="col-12 col-md-6">
                        <x-adminlte-select name="form.barangay" label="Barangay / District" wire:model="form.barangay"
                            igroup-size="sm" :disabled="empty($barangays)">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div>
                            </x-slot>
                            <option value="">— Select Barangay —</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay['name'] }}">{{ $barangay['name'] }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                {{-- ZIP Code + Is Primary --}}
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.zip_code" label="ZIP Code"
                            placeholder="Auto-filled when city is selected"
                            wire:model.defer="form.zip_code" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-mail-bulk"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6 d-flex align-items-end">
                        <div class="form-group w-100">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isPrimary"
                                    wire:model.defer="form.is_primary" value="1">
                                <label class="custom-control-label font-weight-normal" for="isPrimary">
                                    Set as primary address
                                    <small class="text-muted d-block">Used on official documents.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Address' : 'Create Address' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
