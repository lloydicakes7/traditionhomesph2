<div wire:ignore.self>
    <x-adminlte-modal id="memberFormModal"
        title="{{ $isEditing ? 'Edit HOA Member' : 'New HOA Member' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading member form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center" style="gap:1.5rem;">
                            <div class="text-center flex-shrink-0">
                                @if ($memberPhoto)
                                    <img src="{{ $memberPhoto->temporaryUrl() }}" class="rounded-circle elevation-2"
                                        style="width:80px; height:80px; object-fit:cover;">
                                @elseif ($existingMemberPhotoUrl)
                                    <img src="{{ $existingMemberPhotoUrl }}" class="rounded-circle elevation-2"
                                        style="width:80px; height:80px; object-fit:cover;">
                                @else
                                    <img src="{{ asset('image/user.png') }}" class="rounded-circle elevation-2"
                                        style="width:80px; height:80px; object-fit:cover;">
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="font-weight-bold">Profile Photo</label>
                                <p class="text-muted small mb-2">JPG, PNG, GIF. Max 2MB.</p>
                                <div class="d-flex" style="gap:0.5rem;">
                                    <div class="custom-file flex-grow-1">
                                        <input type="file" class="custom-file-input" id="memberPhoto"
                                            wire:model="memberPhoto" accept="image/*">
                                        <label class="custom-file-label" for="memberPhoto">
                                            {{ $memberPhoto ? $memberPhoto->getClientOriginalName() : 'Choose file...' }}
                                        </label>
                                    </div>
                                    @if ($existingMemberPhotoUrl || $memberPhoto)
                                        <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                                            wire:click="removePhoto" wire:loading.attr="disabled" class="btn-sm"
                                            title="Remove photo" />
                                    @endif
                                </div>
                                <div wire:loading wire:target="memberPhoto" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                                @error('memberPhoto') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.name" label="Name"
                            placeholder="e.g., Juan Dela Cruz"
                            wire:model.defer="form.name" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.position" label="Position"
                            placeholder="e.g., President, Treasurer, Committee Head"
                            wire:model.defer="form.position" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-id-badge"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.phone" label="Phone Number" placeholder="e.g., 0917 123 4567 (optional)"
                            wire:model.defer="form.phone" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-phone-alt"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.email" label="Email" type="email"
                            placeholder="e.g., president@traditionhomes.ph (optional)"
                            wire:model.defer="form.email" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.note" label="Note" rows="2"
                            placeholder="Anything worth knowing (optional)"
                            wire:model.defer="form.note" igroup-size="sm" />
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Member' : 'Create Member' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
        function decorateMemberFileInput() {
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }
        $(decorateMemberFileInput);
        Livewire.hook('message.processed', decorateMemberFileInput);
    </script>
    @endpush
</div>
