<div class="card shadow-sm">

    <div class="card-header bg-white d-flex align-items-center">
        <h3 class="card-title mb-0 mr-3">
            <i class="fas fa-fw fa-bell mr-2"></i>Notifications
        </h3>

        @if ($hasUnread)
            <button type="button" class="btn btn-sm btn-outline-secondary ml-auto"
                wire:click="markAllAsRead" wire:loading.attr="disabled">
                <i class="fas fa-fw fa-check-double mr-1"></i>Mark all as read
            </button>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="d-none d-lg-block table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="align-middle" style="width: 30%;">Title</th>
                        <th class="align-middle">Description</th>
                        <th class="align-middle" style="width: 40px;"></th>
                        <th class="align-middle text-right" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notification)
                        <tr wire:key="notification-row-{{ $notification->id }}">
                            <td class="align-middle">
                                <a href="{{ route($readRoute, $notification->id) }}"
                                    class="font-weight-bold text-body">{{ $notification->data['title'] ?? 'Notification' }}</a>
                                <div class="small text-muted">
                                    {{ $notification->created_at ? $notification->created_at->diffForHumans() : 'Time not recorded' }}
                                </div>
                            </td>
                            <td class="align-middle">
                                <div>{{ $notification->data['body'] ?? 'No further details.' }}</div>
                                @if ($deletions[$notification->id] ?? null)
                                    <div class="small text-muted">
                                        Will be deleted on {{ $deletions[$notification->id]->format('M j, Y') }} at {{ $deletions[$notification->id]->format('g:i A') }}
                                    </div>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if (!$notification->read_at)
                                    <span class="badge badge-primary">New</span>
                                @else
                                    <span class="badge badge-success">Read</span>
                                @endif
                            </td>
                            <td class="text-right align-middle text-nowrap">
                                @include('livewire.notifications.partials.notification-actions', ['actionKey' => 'Desktop'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="far fa-fw fa-bell-slash fa-2x mb-2"></i>
                                <p class="mb-0">Nothing here yet. Answers from the office and news from the association will show up here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-block d-lg-none p-2">
            @forelse ($notifications as $notification)
                <div class="card mb-2 shadow-sm {{ $notification->read_at ? '' : 'bg-light' }}"
                    wire:key="notification-card-{{ $notification->id }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="{{ route($readRoute, $notification->id) }}" class="font-weight-bold text-body">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </a>
                            @unless ($notification->read_at)
                                <span class="badge badge-primary ml-2">New</span>
                            @endunless
                        </div>

                        <div class="small text-muted mb-2">
                            {{ $notification->created_at ? $notification->created_at->diffForHumans() : 'Time not recorded' }}
                        </div>

                        <div class="mb-3">
                            <div>{{ $notification->data['body'] ?? 'No further details.' }}</div>
                            @if ($deletions[$notification->id] ?? null)
                                <div class="small text-muted">
                                    Will be deleted on {{ $deletions[$notification->id]->format('M j, Y') }} at {{ $deletions[$notification->id]->format('g:i A') }}
                                </div>
                            @endif
                        </div>

                        <div class="text-right">
                            @include('livewire.notifications.partials.notification-actions', ['actionKey' => 'Mobile'])
                        </div>
                    </div>
                </div>
            @empty
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="far fa-fw fa-bell-slash fa-2x mb-2"></i>
                        <p class="mb-0">Nothing here yet. Answers from the office and news from the association will show up here.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @if ($notifications->isNotEmpty())
        <div class="card-footer bg-white">{{ $notifications->links('notifications.pagination') }}</div>
    @endif

</div>
