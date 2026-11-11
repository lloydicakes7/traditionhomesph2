<div wire:ignore.self>
    <x-adminlte-modal id="bookingDeleteModal"
        title="Delete Booking"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the booking…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($deletingBooking)
                <div class="font-weight-bold">{{ $deletingBooking->amenity->name ?? '—' }}</div>
                <div class="text-muted small">
                    {{ $deletingBooking->date_time_display }} · {{ $deletingBooking->booker_name }}
                </div>
            @endif

            <p class="text-danger small mt-3 mb-0">
                <i class="fas fa-fw fa-info-circle"></i>
                Deleting takes the reservation off the books entirely — cancelling it keeps the record and releases the slot.
                This action cannot be undone.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Delete" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Deleting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
