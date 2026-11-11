<div>
    <div class="mb-4">
        <form wire:submit.prevent role="search" class="mx-auto" style="max-width: 34rem;">
            <x-adminlte-input name="search" placeholder="Search announcements..." aria-label="Search Announcements"
                wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                <x-slot name="prependSlot">
                    <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                </x-slot>
                @if (filled($search))
                    <x-slot name="appendSlot">
                        <button type="button" class="btn btn-secondary" wire:click="clearSearch"
                            title="Clear search" aria-label="Clear search">
                            <i class="fas fa-fw fa-times"></i>
                        </button>
                    </x-slot>
                @endif
            </x-adminlte-input>
        </form>
    </div>

    <x-ui.cards.announcement-cards :announcements="$announcements" :search="$search" />

    @if ($announcements->hasPages())
        <div class="d-flex justify-content-center mt-3 mb-4">
            {{ $announcements->links() }}
        </div>
    @endif
</div>
