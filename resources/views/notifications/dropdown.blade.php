@forelse ($notifications as $notification)
    @include('notifications.item', ['notification' => $notification, 'readRoute' => $readRoute])
@empty
    <span class="dropdown-item text-muted py-3 text-center">Nothing waiting for you.</span>
@endforelse
