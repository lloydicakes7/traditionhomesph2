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

                @include('partials.personal-fields', ['prefix' => 'form'])

                @if (! $isEditing)
                    @include('partials.household-unit-fields')
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
