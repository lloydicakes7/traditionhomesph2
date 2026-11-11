<div wire:ignore.self>
    <x-adminlte-modal id="bookingRefundDepositModal"
        title="Refund Deposit"
        icon="fas fa-fw fa-undo" theme="success" size="md" scrollable v-centered wire:ignore.self>

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
            <x-adminlte-button label="Refund Deposit" theme="success" icon="fas fa-fw fa-undo"
                wire:click="refund" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="refund" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Working...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
