<div wire:ignore.self>
    <x-adminlte-modal id="bookingRequestModal" title="Request a Booking"
        icon="far fa-fw fa-calendar-plus" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Preparing the booking form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form wire:submit.prevent="save">

            {{-- ========================= AMENITY ========================= --}}
            <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
                <i class="fas fa-fw fa-building mr-1"></i> Amenity
            </h6>

            <x-adminlte-select name="form.amenity_id" label="Which amenity would you like to book?"
                wire:model.lazy="form.amenity_id" igroup-size="sm">
                <x-slot name="prependSlot">
                    <div class="input-group-text"><i class="fas fa-fw fa-building"></i></div>
                </x-slot>
                <option value="">Select an amenity...</option>
                @foreach ($amenities as $amenity)
                    <option value="{{ $amenity->id }}">{{ $amenity->name }}</option>
                @endforeach
            </x-adminlte-select>

            @if ($selected)
                <div class="rounded bg-light border px-3 py-2 mb-3">
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Rate</span>
                        <span class="font-weight-bold">
                            {{ $selected->rate_display }}
                            <span class="text-muted font-weight-normal">· {{ $selected->rate_basis_display }}</span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="text-muted">Open</span>
                        <span>{{ $selected->schedule_summary }}</span>
                    </div>
                </div>
            @endif

            {{-- ========================= SCHEDULE ======================== --}}
            <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3 mt-4">
                <i class="far fa-fw fa-calendar-alt mr-1"></i> Schedule
            </h6>

            <x-adminlte-input name="form.date" label="Date" type="date"
                min="{{ now()->toDateString() }}"
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

            <div wire:loading
                wire:target="form.amenity_id, form.date, form.start_time, form.end_time"
                class="small text-info mb-2">
                <i class="fas fa-spinner fa-spin"></i> Calculating…
            </div>

            @if ($preview)
                <div class="rounded border border-info bg-light px-3 py-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Amount for this booking</span>
                        <span class="font-weight-bold text-primary">{{ $preview['amount'] }}</span>
                    </div>
                    <div class="small text-muted mt-1">
                        <i class="fas fa-fw fa-info-circle"></i>
                        Any deposit will be set by the office when it approves your request.
                    </div>
                </div>
            @endif

            {{-- =========================== PURPOSE ======================= --}}
            <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3 mt-4">
                <i class="far fa-fw fa-sticky-note mr-1"></i> Purpose
            </h6>

            <x-adminlte-textarea name="form.purpose" label="What will it be used for?" rows="2"
                placeholder="e.g., Birthday party for 30 guests"
                wire:model.defer="form.purpose" igroup-size="sm" />
        </form>
        </div>

        <x-slot name="footerSlot">
            <span wire:loading wire:target="save" class="mr-auto text-info small">
                <i class="fas fa-spinner fa-spin"></i> Submitting...
            </span>
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Submit Request" theme="primary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
        </x-slot>
    </x-adminlte-modal>
</div>
