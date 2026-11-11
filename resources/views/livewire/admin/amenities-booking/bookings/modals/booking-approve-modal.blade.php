<div wire:ignore.self>
    <x-adminlte-modal id="bookingApproveModal"
        title="Approve Booking"
        icon="fas fa-fw fa-check" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($booking)
                @include('livewire.admin.amenities-booking.bookings.partials.reservation-summary')

                <p class="mb-0">
                    The schedule is held for the homeowner as soon as the booking is approved.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Approve Booking" theme="primary" icon="fas fa-fw fa-check"
                wire:click="approve" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="approve" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Working...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
