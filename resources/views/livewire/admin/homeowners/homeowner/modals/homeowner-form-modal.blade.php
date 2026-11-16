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
                                <br><small class="text-muted">
                                    A deactivated account cannot sign in until it is activated.
                                </small>
                            </label>
                        </div>
                    </div>
                </div>

                @include('partials.personal-fields', ['prefix' => 'form'])

                @include('partials.household-unit-fields', [
                    'canJoin' => $canJoin,
                    'canChangeTenure' => $canChangeTenure,
                ])

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
