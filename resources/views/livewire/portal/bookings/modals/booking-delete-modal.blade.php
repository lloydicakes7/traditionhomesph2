<div wire:ignore.self>
    <x-adminlte-modal id="bookingDeleteModal"
        title="Delete Booking"
        icon="fas fa-fw fa-trash" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($booking)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $booking->amenity->name ?? '—' }}</div>
                    <div class="text-muted small">{{ $booking->date_time_display }}</div>
                    <div class="text-muted small">{{ $booking->reference ?: '—' }}</div>
                </div>

                <p class="mb-0">
                    This removes the booking from your list. The office handles cancellations with a
                    remark for you.
                </p>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Keep Booking" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete Booking" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
