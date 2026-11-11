<div wire:ignore.self>
    <x-adminlte-modal id="amenityFormModal"
        title="{{ $isEditing ? 'Edit Amenity' : 'Add Amenity' }}"
        icon="far fa-fw {{ $isEditing ? 'fa-edit' : 'fa-calendar-check' }}"
        theme="primary" size="lg" v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the amenity form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form>
            {{-- =========================================================== --}}
            {{-- PHOTO                                                       --}}
            {{-- =========================================================== --}}
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="d-flex align-items-center" style="gap:1.5rem;">
                        <div class="text-center flex-shrink-0">
                            @if ($amenityImage)
                                <img src="{{ $amenityImage->temporaryUrl() }}" class="elevation-2"
                                    style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                            @elseif ($existingAmenityImageUrl)
                                <img src="{{ $existingAmenityImageUrl }}" class="elevation-2"
                                    style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                            @else
                                <img src="{{ asset('image/placeholder.jpg') }}" class="elevation-2"
                                    style="width:120px; height:80px; object-fit:cover; border-radius:0.25rem;">
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <label class="font-weight-bold">Amenity Photo</label>
                            <p class="text-muted small mb-2">JPG, PNG, GIF. Max 2MB. Shown at the top of the amenity card and its profile.</p>
                            <div class="d-flex" style="gap:0.5rem;">
                                <div class="custom-file flex-grow-1">
                                    <input type="file" class="custom-file-input" id="amenityImage"
                                        wire:model="amenityImage" accept="image/*">
                                    <label class="custom-file-label" for="amenityImage">
                                        {{ $amenityImage ? $amenityImage->getClientOriginalName() : 'Choose file...' }}
                                    </label>
                                </div>
                                @if ($existingAmenityImageUrl || $amenityImage)
                                    <x-adminlte-button type="button" theme="danger" icon="fas fa-fw fa-trash-alt"
                                        wire:click="removeImage" wire:loading.attr="disabled" class="btn-sm"
                                        title="Remove photo" />
                                @endif
                            </div>
                            <div wire:loading wire:target="amenityImage" class="mt-1">
                                <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                            </div>
                            @error('amenityImage') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================== --}}
            {{-- DETAILS                                                     --}}
            {{-- =========================================================== --}}
            <div class="row">
                <div class="col-12">
                    <x-adminlte-input name="form.name" label="Amenity Name" placeholder="e.g., Clubhouse"
                        wire:model.defer="form.name" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-building"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-7">
                    <x-adminlte-input name="form.rate" label="Rate" type="number" step="0.01" min="0"
                        placeholder="0.00 (Leave 0 to labeled free)" wire:model.defer="form.rate" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">{{ $currencySymbol }}</div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-sm-5">
                    <x-adminlte-select name="form.rate_type" label="Rate Type" wire:model.defer="form.rate_type"
                        igroup-size="sm">
                        @foreach ($rateTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>
            </div>

            {{-- =========================================================== --}}
            {{-- WEEKLY SCHEDULE                                             --}}
            {{-- =========================================================== --}}
            <div class="form-group">
                <label class="font-weight-bold mb-1">Weekly Schedule</label>

                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width:28%;">Day</th>
                                <th style="width:16%;">Available</th>
                                <th style="width:28%;">Opens</th>
                                <th style="width:28%;">Closes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weekdays as $value => $label)
                                <tr>
                                    <td class="align-middle font-weight-medium">{{ $label }}</td>
                                    <td class="text-center align-middle">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input"
                                                id="amenityDay{{ $value }}"
                                                wire:model="schedule.{{ $value }}.enabled">
                                            <label class="custom-control-label" for="amenityDay{{ $value }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($schedule[$value]['enabled'] ?? true)
                                            <x-adminlte-input type="time" name="schedule.{{ $value }}.opens_at"
                                                wire:model.defer="schedule.{{ $value }}.opens_at"
                                                igroup-size="sm" fgroup-class="mb-0" />
                                        @else
                                            <span class="text-muted small">Closed</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($schedule[$value]['enabled'] ?? true)
                                            <x-adminlte-input type="time" name="schedule.{{ $value }}.closes_at"
                                                wire:model.defer="schedule.{{ $value }}.closes_at"
                                                igroup-size="sm" fgroup-class="mb-0" />
                                            @error('schedule.' . $value . '.closes_at')
                                                <span class="text-danger small d-block">{{ $message }}</span>
                                            @enderror
                                        @else
                                            <span class="text-muted small">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- =========================================================== --}}
            {{-- HOW THE PORTAL SEES IT                                      --}}
            {{-- Every reservation is approved by the office before it can be --}}
            {{-- used, so the only question here is whether the portal offers --}}
            {{-- the facility at all.                                         --}}
            {{-- =========================================================== --}}
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="amenityIsBookable"
                        wire:model.defer="form.is_bookable">
                    <label class="custom-control-label" for="amenityIsBookable">
                        Homeowner Portal
                        <br><small>{{ $form['is_bookable'] ? 'Bookable by homeowners' : 'Office books it only' }}</small>
                    </label>
                </div>
            </div>

            {{-- =========================================================== --}}
            {{-- DESCRIPTION                                                 --}}
            {{-- =========================================================== --}}
            <div class="row">
                <div class="col-12">
                    <x-adminlte-textarea name="form.description" label="Description" rows="3"
                        placeholder="What the facility is and what it is used for."
                        wire:model.defer="form.description" igroup-size="sm" />
                </div>
            </div>
        </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Amenity' : 'Add Amenity' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>

    {{-- The photo field is a Bootstrap custom-file input, which has to be
         decorated each time Livewire paints the form. --}}
    @push('js')
    <script>
        function decorateAmenityFileInput() {
            if (typeof bsCustomFileInput !== 'undefined') {
                bsCustomFileInput.init();
            }
        }
        $(decorateAmenityFileInput);
        Livewire.hook('message.processed', decorateAmenityFileInput);
    </script>
    @endpush
</div>
