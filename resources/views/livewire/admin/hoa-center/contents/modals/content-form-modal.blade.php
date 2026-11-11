<div wire:ignore.self>
    <x-adminlte-modal id="contentFormModal"
        title="{{ $isEditing ? 'Edit Page' : 'New Page' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading page form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- ===== Image ===== --}}
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-4">
                    <div class="text-center mr-0 mr-sm-3 mb-2 mb-sm-0" style="flex: 0 0 120px;">
                        @if ($contentImage)
                            <img src="{{ $contentImage->temporaryUrl() }}" class="elevation-2 rounded"
                                style="width: 120px; height: 90px; object-fit: cover;">
                        @elseif ($existingContentImageUrl)
                            <img src="{{ $existingContentImageUrl }}" class="elevation-2 rounded"
                                style="width: 120px; height: 90px; object-fit: cover;">
                        @else
                            <img src="{{ asset('image/placeholder.jpg') }}" class="elevation-2 rounded"
                                style="width: 120px; height: 90px; object-fit: cover;">
                        @endif
                    </div>

                    <div class="flex-grow-1 w-100">
                        <label class="font-weight-bold">Page Image</label>
                        <p class="text-muted small mb-2">JPG, PNG, GIF or WEBP. Max 2MB. Shown at the top of the page.</p>

                        <div class="d-flex">
                            <div class="custom-file flex-grow-1">
                                <input type="file" class="custom-file-input" id="contentImage"
                                    wire:model="contentImage" accept="image/*">
                                <label class="custom-file-label" for="contentImage">
                                    {{ $contentImage ? $contentImage->getClientOriginalName() : 'Choose file...' }}
                                </label>
                            </div>

                            @if ($existingContentImageUrl || $contentImage)
                                <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                                    wire:click="removeImage" wire:loading.attr="disabled" class="btn-sm ml-2"
                                    title="Remove image" />
                            @endif
                        </div>

                        <div wire:loading wire:target="contentImage" class="mt-1">
                            <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                        </div>

                        @error('contentImage') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- ===== Title and group ===== --}}
                <div class="row">
                    <div class="col-12 col-md-8">
                        <x-adminlte-input name="form.title" label="Title" maxlength="150"
                            placeholder="e.g., Mission & Vision" wire:model.defer="form.title" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-heading"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>

                    <div class="col-12 col-md-4">
                        <x-adminlte-input name="form.group" label="Group (optional)" maxlength="100"
                            placeholder="e.g., Environment" wire:model.defer="form.group" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-layer-group"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                @if ($editingContentSlug)
                    <x-adminlte-input name="contentSlug" label="Page URL" readonly
                        value="{{ route('content.show', $editingContentSlug) }}" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-link"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                @endif

                {{-- ===== Details ===== --}}
                <div class="form-group mb-0">
                    <label for="contentDetails">Details</label>

                    <div wire:ignore wire:key="content-details-editor">
                        <div data-rich-text="contentDetails" data-rich-text-height="260"
                            data-rich-text-placeholder="Write the page. Use the toolbar to add headings, lists and emphasis."></div>
                    </div>

                    <textarea id="contentDetails" class="d-none" wire:model.defer="form.details"></textarea>

                    @error('form.details') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Page' : 'Create Page' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
