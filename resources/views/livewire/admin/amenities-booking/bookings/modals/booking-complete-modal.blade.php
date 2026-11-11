<div wire:ignore.self>
    <x-adminlte-modal id="bookingCompleteModal"
        title="Complete Booking"
        icon="fas fa-fw fa-flag-checkered" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($booking)
                @include('livewire.admin.amenities-booking.bookings.partials.reservation-summary')

                <p class="mb-0">
                    The booking is closed as used and its slot is released.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Complete Booking" theme="primary" icon="fas fa-fw fa-flag-checkered"
                wire:click="complete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="complete" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Working...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
