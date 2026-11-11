<div class="card shadow-sm">

    <div class="card-header bg-white d-flex align-items-center">
        <h3 class="card-title mb-0 mr-3">
            <i class="fas fa-fw fa-bell mr-2"></i>Notifications
        </h3>

        {{-- Pinned to the header's right corner with `ml-auto`, never
             `justify-content-between`: an AdminLTE `.card-header` is a
             clearfix, so its `::after` is a third flex item and spacing the
             ends apart lands the button in the middle. --}}
        @if ($hasUnread)
            <button type="button" class="btn btn-sm btn-outline-secondary ml-auto"
                wire:click="markAllAsRead" wire:loading.attr="disabled">
                <i class="fas fa-fw fa-check-double mr-1"></i>Mark all as read
            </button>
        @endif
    </div>

    <div class="card-body p-1">
        @forelse ($notifications as $notification)
            @include('notifications.item', ['notification' => $notification, 'readRoute' => $readRoute])
        @empty
            <div class="text-center text-muted py-5">
                <i class="far fa-fw fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">Nothing here yet — the office's answers and the association's news land here.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->isNotEmpty())
        <div class="card-footer bg-white">{{ $notifications->links('notifications.pagination') }}</div>
    @endif

</div>
