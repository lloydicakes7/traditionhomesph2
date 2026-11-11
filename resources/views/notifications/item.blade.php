@php($type = \App\Enums\NotificationType::tryFrom($notification->data['type'] ?? ''))

<a href="{{ route($readRoute, $notification->id) }}"
   class="dropdown-item text-wrap d-flex align-items-start px-3 py-3 text-body {{ $notification->read_at ? '' : 'bg-light' }}">

    <span class="badge badge-{{ $type ? $type->badgeTheme() : 'secondary' }} p-2 mr-3 flex-shrink-0">
        <i class="{{ $type ? $type->icon() : 'fas fa-fw fa-bell' }}"></i>
    </span>

    <span class="notification-text flex-grow-1">
        <strong class="d-block text-xs font-weight-bold text-truncate {{ $notification->read_at ? '' : 'text-dark' }}">
            {{ $notification->data['title'] ?? 'Notification' }}
        </strong>
        <small class="d-block text-muted text-truncate">{{ $notification->data['body'] ?? '' }}</small>
        <small class="text-muted">{{ $notification->created_at ? $notification->created_at->diffForHumans() : '' }}</small>
    </span>

    @unless ($notification->read_at)
        <span class="badge badge-danger ml-2 flex-shrink-0">New</span>
    @endunless

</a>
