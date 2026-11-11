<div wire:ignore.self>
    <x-adminlte-modal id="bookingForfeitDepositModal"
        title="Forfeit Deposit"
        icon="fas fa-fw fa-ban" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($booking)
                @include('livewire.admin.amenities-booking.bookings.partials.reservation-summary')

                <p class="mb-0">
                    The deposit is returned to the homeowner after inspection.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Forfeit Deposit" theme="danger" icon="fas fa-fw fa-ban"
                wire:click="forfeit" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="forfeit" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Working...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
