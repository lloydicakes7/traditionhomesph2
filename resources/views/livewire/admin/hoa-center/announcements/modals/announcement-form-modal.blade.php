<div wire:ignore.self>
    <x-adminlte-modal id="announcementFormModal"
        title="{{ $isEditing ? 'Edit Announcement' : 'New Announcement' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading announcement form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center" style="gap:1.5rem;">
                            <div class="text-center flex-shrink-0">
                                @if ($announcementImage)
                                    <img src="{{ $announcementImage->temporaryUrl() }}" class="elevation-2"
                                        style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                                @elseif ($existingAnnouncementImageUrl)
                                    <img src="{{ $existingAnnouncementImageUrl }}" class="elevation-2"
                                        style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                                @else
                                    <img src="{{ asset('image/placeholder.jpg') }}" class="elevation-2"
                                        style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <label class="font-weight-bold">Announcement Image</label>
                                <p class="text-muted small mb-2">JPG, PNG, GIF. Max 2MB. Shown at the top of the post card.</p>
                                <div class="d-flex" style="gap:0.5rem;">
                                    <div class="custom-file flex-grow-1">
                                        <input type="file" class="custom-file-input" id="announcementImage"
                                            wire:model="announcementImage" accept="image/*">
                                        <label class="custom-file-label" for="announcementImage">
                                            {{ $announcementImage ? $announcementImage->getClientOriginalName() : 'Choose file...' }}
                                        </label>
                                    </div>
                                    @if ($existingAnnouncementImageUrl || $announcementImage)
                                        <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                                            wire:click="removeImage" wire:loading.attr="disabled" class="btn-sm"
                                            title="Remove image" />
                                    @endif
                                </div>
                                <div wire:loading wire:target="announcementImage" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                                @error('announcementImage') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Title --}}
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-input name="form.title" label="Title"
                            placeholder="e.g., Water Interruption on Maple Street, September 20"
                            wire:model.defer="form.title" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-heading"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                {{-- Body --}}
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.body" label="Details" rows="8"
                            placeholder="Write the announcement. Blank lines start a new paragraph on the post."
                            wire:model.defer="form.body" igroup-size="sm" />
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Announcement' : 'Publish Announcement' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
        function decorateAnnouncementFileInput() {
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }
        $(decorateAnnouncementFileInput);
        Livewire.hook('message.processed', decorateAnnouncementFileInput);
    </script>
    @endpush
</div>
