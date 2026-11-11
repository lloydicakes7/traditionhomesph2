<div wire:ignore.self>
    <x-adminlte-modal id="bookingFormModal"
        title="{{ $isEditing ? 'Edit Booking' : 'Add Booking' }}"
        icon="far fa-fw {{ $isEditing ? 'fa-edit' : 'fa-calendar-plus' }}"
        theme="primary" size="md" v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form wire:submit.prevent="save">

            <div class="form-group mb-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="bookingIsGuest"
                        wire:model="isGuest">
                    <label class="custom-control-label" for="bookingIsGuest">
                        Guest booking
                        <small class="d-block text-muted">
                            {{ $isGuest
                                ? 'For someone outside the association'
                                : 'For a homeowner account on file' }}
                        </small>
                    </label>
                </div>
            </div>

            @if ($isGuest)
                <x-adminlte-input name="form.guest_name" label="Guest"
                    placeholder="e.g., Dela Cruz reunion"
                    wire:model.defer="form.guest_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-user-friends"></i></div>
                    </x-slot>
                </x-adminlte-input>
            @else
                <x-adminlte-select2 id="bookingFormUserId" name="form.user_id" label="Homeowner"
                    wire:model.defer="form.user_id" igroup-size="sm"
                    :config="['placeholder' => 'Select a homeowner account...']">
                    <option value="">Select a homeowner account...</option>
                    @foreach ($owners as $owner)
                        <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                    @endforeach
                </x-adminlte-select2>
            @endif

            <x-adminlte-select name="form.amenity_id" label="Amenity"
                wire:model.lazy="form.amenity_id" igroup-size="sm">
                <x-slot name="prependSlot">
                    <div class="input-group-text"><i class="fas fa-fw fa-building"></i></div>
                </x-slot>
                <option value="">Select an amenity...</option>
                @foreach ($amenities as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </x-adminlte-select>

            <x-adminlte-input name="form.date" label="Date" type="date"
                wire:model.lazy="form.date" igroup-size="sm">
                <x-slot name="prependSlot">
                    <div class="input-group-text"><i class="far fa-fw fa-calendar-alt"></i></div>
                </x-slot>
            </x-adminlte-input>

            <div class="row">
                <div class="col-6">
                    <x-adminlte-input name="form.start_time" label="Starts" type="time"
                        wire:model.lazy="form.start_time" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="far fa-fw fa-clock"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-6">
                    <x-adminlte-input name="form.end_time" label="Ends" type="time"
                        wire:model.lazy="form.end_time" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="far fa-fw fa-clock"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            </div>

            <x-adminlte-input name="form.deposit_amount" label="Refundable Deposit" type="number"
                step="0.01" min="0" placeholder="0.00"
                wire:model.debounce.500ms="form.deposit_amount" igroup-size="sm">
                <x-slot name="prependSlot">
                    <div class="input-group-text">{{ $currencySymbol }}</div>
                </x-slot>
            </x-adminlte-input>

            <div wire:loading
                wire:target="form.amenity_id, form.date, form.start_time, form.end_time, form.deposit_amount"
                class="small text-info mb-2">
                <i class="fas fa-spinner fa-spin"></i> Calculating…
            </div>

            @if ($amountPreview)
                <div class="rounded bg-light border px-3 py-2 mb-2">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Amount to pay</span>
                        <span>{{ $amountPreview }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-muted">Refundable deposit</span>
                        <span>{{ $depositPreview }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total</span>
                        <span class="text-primary">{{ $totalPreview }}</span>
                    </div>
                </div>
            @endif

            <x-adminlte-textarea name="form.purpose" label="Purpose" rows="2"
                placeholder="What the amenity will be used for."
                wire:model.defer="form.purpose" igroup-size="sm" />

            <x-adminlte-textarea name="form.remarks" label="Office Remarks" rows="2"
                placeholder="Anything the office needs to note about this booking."
                wire:model.defer="form.remarks" igroup-size="sm" />
        </form>
        </div>

        <x-slot name="footerSlot">
            <span wire:loading wire:target="save" class="mr-auto text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Booking' : 'Add Booking' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save"
                wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
        </x-slot>
    </x-adminlte-modal>
</div>
