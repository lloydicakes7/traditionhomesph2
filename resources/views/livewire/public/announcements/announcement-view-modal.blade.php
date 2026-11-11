<div wire:ignore.self>
    <x-adminlte-modal id="announcementViewModal"
        title="{{ $announcement->title ?? 'Announcement' }}"
        icon="fas fa-fw fa-bullhorn" size="lg"
        v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the announcement…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($announcement)
                @if ($announcement->image_url)
                    <img src="{{ $announcement->image_url }}" alt="{{ $announcement->title }}"
                        class="w-100 rounded mb-3" style="max-height: 320px; object-fit: cover;">
                @endif

                <div class="mb-3">
                    <h6 class="mb-0 font-weight-bold">{{ $announcement->title }}</h6>
                    <small class="text-muted">
                        <i class="far fa-fw fa-calendar-alt mr-1"></i>{{ $announcement->display_date }}
                    </small>
                </div>

                <div style="line-height: 1.7;">{!! nl2br(e($announcement->body)) !!}</div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
