<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="notificationActions{{ $actionKey }}{{ $notification->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>

    <div class="dropdown-menu dropdown-menu-right"
        aria-labelledby="notificationActions{{ $actionKey }}{{ $notification->id }}">

        @if ($notification->read_at)
            <a class="dropdown-item disabled" href="#" tabindex="-1" aria-disabled="true">
                <i class="fas fa-fw fa-check mr-1"></i> Read
            </a>
        @else
            <a class="dropdown-item" href="#"
                wire:click.prevent="markAsRead('{{ $notification->id }}')" wire:loading.attr="disabled">
                <i class="fas fa-fw fa-check mr-1"></i> Read
            </a>
        @endif

        <div class="dropdown-divider"></div>

        <a class="dropdown-item text-danger" href="#"
            wire:click.prevent="delete('{{ $notification->id }}')" wire:loading.attr="disabled">
            <i class="fas fa-fw fa-trash mr-1"></i> Delete
        </a>
    </div>
</div>
