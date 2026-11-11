<div wire:ignore.self>
    <x-adminlte-modal id="propertyFormModal"
        title="{{ $isEditing ? 'Edit Property' : 'Add New Property' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading property form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- Picture Upload --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center" style="gap:1.5rem;">
                            <div class="text-center flex-shrink-0">
                                @if ($propertyPicture)
                                    <img src="{{ $propertyPicture->temporaryUrl() }}" class="rounded elevation-2"
                                        style="width:100px; height:75px; object-fit:cover;">
                                @elseif ($existingPropertyPhotoUrl)
                                    <img src="{{ $existingPropertyPhotoUrl }}" class="rounded elevation-2"
                                        style="width:100px; height:75px; object-fit:cover;">
                                @else
                                    <img src="{{ asset('image/house.png') }}" class="rounded elevation-2"
                                        style="width:100px; height:75px; object-fit:cover;">
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="font-weight-bold">Property Picture</label>
                                <p class="text-muted small mb-2">JPG, PNG, GIF. Max 2MB.</p>
                                <div class="d-flex" style="gap:0.5rem;">
                                    <div class="custom-file flex-grow-1">
                                        <input type="file" class="custom-file-input" id="propertyPicture"
                                            wire:model="propertyPicture" accept="image/*">
                                        <label class="custom-file-label" for="propertyPicture">
                                            {{ $propertyPicture ? $propertyPicture->getClientOriginalName() : 'Choose file...' }}
                                        </label>
                                    </div>
                                    @if ($existingPropertyPhotoUrl || $propertyPicture)
                                        <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                                            wire:click="removePropertyPictureAction" wire:loading.attr="disabled" class="btn-sm"
                                            title="Remove picture" />
                                    @endif
                                </div>
                                <div wire:loading wire:target="propertyPicture" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                                @error('propertyPicture') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <x-adminlte-input name="form.block" label="Block" type="number" min="1" placeholder="e.g., 1"
                            wire:model.defer="form.block" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-cube"></i></div></x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-sm-6">
                        <x-adminlte-input name="form.lot" label="Lot" type="number" min="1" placeholder="e.g., 12"
                            wire:model.defer="form.lot" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-cube"></i></div></x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <x-adminlte-input name="form.sqm" label="SQM" type="number" step="0.01" placeholder="e.g., 120.50"
                            wire:model.defer="form.sqm" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ruler-combined"></i></div></x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-sm-6">
                        <x-adminlte-select name="form.type" label="Type" wire:model.defer="form.type" igroup-size="sm">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-building"></i></div></x-slot>
                            <option value="">Select...</option>
                            @if(!$loaded)
                                <option value="" disabled>No types available</option>
                            @else
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            @endif
                        </x-adminlte-select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <x-adminlte-select2 id="propertyFormAddressId" name="form.address_id" label="Address"
                            wire:model.defer="form.address_id" igroup-size="sm"
                            :config="['placeholder' => 'Select an address...']">
                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-marker-alt"></i></div></x-slot>
                            <option value="">Select an address...</option>
                            @if (!$loaded)
                                <option value="" disabled>No addresses available</option>
                            @else
                                @foreach ($addresses as $address)
                                    <option value="{{ $address->id }}">
                                        {{ $address->label }} — {{ $address->subdivision }}, {{ $address->barangay }}, {{ $address->city }}
                                    </option>
                                @endforeach
                            @endif
                        </x-adminlte-select2>
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Property' : 'Create Property' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
        (function () {
            function initPropertyFileInput() {
                if (typeof bsCustomFileInput !== 'undefined') {
                    bsCustomFileInput.init();
                }
            }

            document.addEventListener('DOMContentLoaded', initPropertyFileInput);
            window.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', initPropertyFileInput);
            });
        })();
    </script>
    @endpush
</div>
