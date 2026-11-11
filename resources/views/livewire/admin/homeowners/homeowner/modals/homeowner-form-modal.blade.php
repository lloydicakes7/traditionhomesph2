<div wire:ignore.self>
    <x-adminlte-modal id="homeownerFormModal"
        title="{{ $editing ? 'Edit Homeowner' : 'Add New Homeowner' }}"
        icon="fas fa-fw {{ $editing ? 'fa-user-edit' : 'fa-user-plus' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading homeowner form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                @include('livewire.admin.homeowners.partials.profile-photo-field', [
                    'prop' => 'profilePhoto',
                    'domId' => 'homeownerProfilePhoto',
                    'photo' => $profilePhoto,
                    'existingUrl' => $existingPhotoUrl,
                    'removeAction' => 'removePhoto',
                ])

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
                        <i class="fas fa-fw fa-key mr-1"></i>Account
                    </h6>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.email" label="Email *" placeholder="Enter email address"
                                wire:model.defer="form.email" igroup-size="sm" enable-old-support>
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" id="homeownerIsActive"
                                    wire:model.defer="form.is_active">
                                <label class="custom-control-label" for="homeownerIsActive">
                                    Account activated
                                    <br><small class="small">
                                        A deactivated account cannot sign in until it is activated.
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                @include('livewire.admin.homeowners.partials.personal-info-fields', ['prefix' => 'form'])

                @include('livewire.admin.homeowners.partials.rbi-profile-fields', [
                    'prefix' => 'form',
                    'sectorOptions' => $sectorOptions,
                ])

                <div class="mt-4 mb-2">
                    <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
                        <i class="fas fa-fw fa-map-marker-alt mr-1"></i>Address
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

                        @if ($unitNamed && (! $unit || ! $unit['is_occupied'] || $ownHousehold))
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

                    @if ($ownHousehold)
                        <div class="alert alert-info mb-0 py-2">
                            <p class="mb-0 small">
                                This is the address on file for this household. Change the block and lot to move
                                it to another unit.
                            </p>
                        </div>
                    @elseif ($unit && $unit['is_occupied'] && ! $editing)
                        <div class="alert alert-info mb-0">
                            <p class="mb-1">
                                Someone is already registered at this address. Does the account be linked to it?
                            </p>
                            <p class="mb-2 small">
                                <strong>Household head:</strong> {{ $unit['head_name'] ?? '—' }}
                                <span class="text-muted">· {{ $unit['member_count'] }} member(s) on file</span>
                            </p>

                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="linkHomeownerHousehold"
                                    wire:model.defer="household.link_household">
                                <label class="custom-control-label" for="linkHomeownerHousehold">
                                    I live in this household. Link my registration to it.
                                </label>
                            </div>
                            @error('household.link_household')
                                <span class="d-block text-danger small mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    @elseif ($unit && $unit['is_occupied'])
                        <div class="alert alert-info mb-0 py-2">
                            <p class="mb-0 small">
                                Another household already lives at this address. The household on file moves with
                                this homeowner, so choose a unit that is free.
                            </p>
                        </div>
                    @elseif ($unit)
                        <div class="alert alert-info mb-0 py-2">
                            <p class="mb-0 small">
                                No household is registered at this address yet. This homeowner will be recorded
                                as its household head.
                            </p>
                        </div>
                    @elseif ($unitNamed)
                        <div class="alert alert-info mb-0 py-2">
                            <p class="mb-0 small">
                                No unit is on file at this block and lot yet. It will be created under the
                                association address, and this homeowner will head its household.
                            </p>
                        </div>
                    @elseif ($editing)
                        <div class="alert alert-info mb-0 py-2">
                            <p class="mb-0 small">
                                No unit is on file for this homeowner yet. Name the block and lot to place the
                                household.
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
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <div class="d-flex justify-content-between w-100">
                <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
                <div>
                    <x-adminlte-button label="{{ $editing ? 'Save Changes' : 'Create Homeowner' }}"
                        theme="success" icon="fas fa-fw fa-save" wire:click="save"
                        wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
                    <span wire:loading wire:target="save" class="ml-2 text-success small">
                        <i class="fas fa-spinner fa-spin"></i> Saving...
                    </span>
                </div>
            </div>
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
        (function () {
            function initHomeownerFileInput() {
                if (typeof bsCustomFileInput !== 'undefined') {
                    bsCustomFileInput.init();
                }
            }

            document.addEventListener('DOMContentLoaded', initHomeownerFileInput);
            window.addEventListener('livewire:load', function () {
                Livewire.hook('message.processed', initHomeownerFileInput);
            });
        })();
    </script>
    @endpush
</div>
