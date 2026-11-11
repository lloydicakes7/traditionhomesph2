<div wire:ignore.self>
    <x-adminlte-modal id="bookingCancelModal"
        title="Cancel Booking"
        icon="fas fa-fw fa-times-circle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($booking)
                @include('livewire.admin.amenities-booking.bookings.partials.reservation-summary')

                <p class="mb-3">
                    The reservation is withdrawn and the window it was holding is released.
                </p>

                <x-adminlte-textarea name="remarks" label="Reason (optional)" rows="3"
                    placeholder="Why is this reservation being cancelled?"
                    wire:model.defer="remarks" igroup-size="sm" />
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Keep Booking" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Cancel Booking" theme="danger" icon="fas fa-fw fa-times-circle"
                wire:click="cancel" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="cancel" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Working...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
