<div wire:ignore.self>
    <x-adminlte-modal id="complaintRequestModal" title="File a Complaint"
        icon="fas fa-fw fa-question-circle" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Preparing the complaint form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form>
            {{-- =========================================================== --}}
            {{-- WHAT IT IS ABOUT                                            --}}
            {{-- =========================================================== --}}
            <div class="row">
                <div class="col-12">
                    <x-adminlte-select name="form.category" label="Category"
                        wire:model="form.category" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                        </x-slot>
                        <option value="">Select a category...</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>

                @if (($form['category'] ?? '') === \App\Enums\ComplaintCategory::OTHER)
                    <div class="col-12">
                        <x-adminlte-input name="form.other_description" label="Specify Complaint"
                            maxlength="255" placeholder="Describe the complaint type"
                            wire:model.defer="form.other_description" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-pen"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                @endif

                <div class="col-12">
                    <x-adminlte-input name="form.subject" label="Subject"
                        placeholder="One line saying what this is about"
                        wire:model.defer="form.subject" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-heading"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                <div class="col-12">
                    <x-adminlte-textarea name="form.description" label="Description" rows="5"
                        placeholder="What happened — when and where, what you saw, and anything else the office needs to look into it."
                        wire:model.defer="form.description" igroup-size="sm" />
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label for="complaintImages" class="font-weight-bold">Image Evidence</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="complaintImages"
                            wire:model="images" accept=".jpg,.jpeg,.png,.webp" multiple>
                        <label class="custom-file-label" for="complaintImages">Choose images...</label>
                    </div>
                    <small class="text-muted">JPG, JPEG, PNG or WEBP. Up to 5 images, 5 MB each.</small>

                    <div wire:loading wire:target="images" class="mt-1">
                        <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading images...</small>
                    </div>

                    @error('images') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    @error('images.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror

                    @if (! empty($images))
                        <ul class="list-group list-group-flush mt-2">
                            @foreach ($images as $index => $image)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2"
                                    wire:key="complaint-image-{{ $index }}">
                                    <span class="small text-truncate pr-2">
                                        <i class="fas fa-fw fa-image text-muted mr-1"></i>
                                        {{ $image->getClientOriginalName() }}
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                        wire:click="removeImage({{ $index }})">
                                        Remove
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="col-12 mt-3">
                    <label for="complaintFiles" class="font-weight-bold">Supporting Files</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="complaintFiles"
                            wire:model="files" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.jpg,.jpeg,.png,.webp" multiple>
                        <label class="custom-file-label" for="complaintFiles">Choose files...</label>
                    </div>
                    <small class="text-muted">PDF, Word, Excel, text or image files. Up to 5 files, 10 MB each.</small>

                    <div wire:loading wire:target="files" class="mt-1">
                        <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading files...</small>
                    </div>

                    @error('files') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    @error('files.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror

                    @if (! empty($files))
                        <ul class="list-group list-group-flush mt-2">
                            @foreach ($files as $index => $file)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2"
                                    wire:key="complaint-file-{{ $index }}">
                                    <span class="small text-truncate pr-2">
                                        <i class="fas fa-fw fa-paperclip text-muted mr-1"></i>
                                        {{ $file->getClientOriginalName() }}
                                    </span>
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                        wire:click="removeFile({{ $index }})">
                                        Remove
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="alert alert-info small mb-0 mt-3">
                <i class="fas fa-fw fa-info-circle mr-1"></i>
                Your complaint is filed under your own household record. The office reviews it and posts a
                response on this page, so you can follow it here.
            </div>
        </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Submit Complaint" theme="primary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Submitting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
