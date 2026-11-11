<div wire:ignore.self>
    <x-adminlte-modal id="residentFormModal"
        title="{{ $isEditing ? 'Edit Resident' : 'Add New Resident' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading resident form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                @include('livewire.admin.homeowners.partials.profile-photo-field', [
                    'prop' => 'profilePhoto',
                    'domId' => 'residentPhoto',
                    'photo' => $profilePhoto,
                    'existingUrl' => $existingPhotoUrl,
                    'removeAction' => 'removePhoto',
                ])

                @include('livewire.admin.homeowners.partials.personal-info-fields', ['prefix' => 'form'])

                @include('livewire.admin.homeowners.partials.rbi-profile-fields', [
                    'prefix' => 'form',
                    'sectorOptions' => $sectorOptions,
                ])

                {{-- ========================================================= --}}
                {{-- HOUSEHOLD                                                  --}}
                {{-- ========================================================= --}}
                @if (! $isEditing)
                    <div class="mt-4 mb-2">
                        <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
                            <i class="fas fa-fw fa-home mr-1"></i>Address
                        </h6>

                        @php $unitNamed = $household['block'] !== '' && $household['lot'] !== ''; @endphp

                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">Associated Address</label>
                            <p class="form-control-plaintext border rounded bg-light px-2 py-1 mb-0 small">
                                <i class="fas fa-fw fa-map-marker-alt mr-1"></i>
                                {{ $unit ? $unit['address'] : ($associationAddress ? $associationAddress->full_address : '—') }}
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <x-adminlte-input name="household.block" type="number" min="1" label="BLK"
                                    placeholder="BLK" wire:model.lazy="household.block" igroup-size="sm" />
                            </div>
                            <div class="col-6">
                                <x-adminlte-input name="household.lot" type="number" min="1" label="LOT"
                                    placeholder="LOT" wire:model.lazy="household.lot" igroup-size="sm" />
                            </div>

                            @if ($unit && $unit['is_occupied'])
                                <div class="col-12 col-md-6">
                                    <x-adminlte-input name="household.relationship"
                                        label="Relationship to the Household Head" maxlength="100"
                                        placeholder="e.g., Son, Mother, Uncle"
                                        wire:model.defer="household.relationship" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-users"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                            @elseif ($unitNamed)
                                <div class="col-12 col-md-6">
                                    <x-adminlte-select name="household.type" label="Tenure Type"
                                        wire:model.defer="household.type" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-key"></i></div></x-slot>
                                        @foreach ($householdTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                                <div class="col-12">
                                    <x-adminlte-textarea name="household.prev_address" label="Previous Address"
                                        rows="2" maxlength="500" placeholder="Optional"
                                        wire:model.defer="household.prev_address" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-signs"></i></div></x-slot>
                                    </x-adminlte-textarea>
                                </div>
                            @endif
                        </div>

                        @if ($unit && $unit['is_occupied'])
                            <div class="alert alert-info mb-0">
                                <p class="mb-1">
                                    This resident will join the household already on file at this address once the
                                    link below is confirmed.
                                </p>
                                <p class="mb-2 small">
                                    <strong>Household head:</strong> {{ $unit['head_name'] ?? '—' }}
                                    <span class="text-muted">· {{ $unit['member_count'] }} member(s) on file</span>
                                </p>

                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="linkWalkInHousehold"
                                        wire:model.defer="household.link_household">
                                    <label class="custom-control-label" for="linkWalkInHousehold">
                                        Link this resident to the existing household
                                    </label>
                                </div>
                                @error('household.link_household')
                                    <span class="d-block text-danger small mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif ($unit)
                            <div class="alert alert-info mb-0 py-2">
                                <p class="mb-0 small">
                                    No household is registered at this address yet. This resident will be
                                    recorded as its household head.
                                </p>
                            </div>
                        @elseif ($unitNamed)
                            <div class="alert alert-info mb-0 py-2">
                                <p class="mb-0 small">
                                    No unit is on file at this block and lot yet. It will be created under the
                                    association address, and this resident will head its household.
                                </p>
                            </div>
                        @endif

                        @error('household.block')
                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                        @enderror
                        @error('household.lot')
                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                        @enderror
                        @error('household.type')
                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                        @enderror
                        @error('household.prev_address')
                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                        @enderror
                        @error('household.relationship')
                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Resident' : 'Create Resident' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>

    {{-- Decorate the file input after each Livewire render (the form is loaded lazily). --}}
    @push('js')
    <script>
        (function () {
            function initResidentFileInput() {
                if (typeof bsCustomFileInput !== 'undefined') {
                    bsCustomFileInput.init();
                }
            }

            document.addEventListener('DOMContentLoaded', initResidentFileInput);
            window.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', initResidentFileInput);
            });
        })();
    </script>
    @endpush
</div>
