<div class="btn-group btn-group-sm" role="group" aria-label="Actions">
    @if ($booking->is_approved)
        <a href="{{ route('portal.bookings.slip', $booking->id) }}"
            class="btn btn-xs btn-secondary" title="{{ __('slips.download') }}">
            <i class="fas fa-fw fa-file-pdf"></i>
        </a>
    @endif

    @can('deleteOwn', $booking)
        <x-adminlte-button theme="danger" icon="fas fa-fw fa-trash"
            data-toggle="modal" data-target="#bookingDeleteModal"
            data-id="{{ $booking->id }}" class="btn-xs" />
    @endcan
</div>
