<div>
    <div class="mb-4">
        <form wire:submit.prevent role="search" class="mx-auto" style="max-width: 34rem;">
            <x-adminlte-input name="search" placeholder="Search members..." aria-label="Search members"
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

    @forelse ($groups as $group)
        <section class="mb-5" wire:key="public-member-group-{{ $group->section_key }}">
            <div class="text-center mb-3">
                <h2 class="h4 font-weight-bold mb-0">{{ $group->name }}</h2>
            </div>

            <x-ui.cards.member-cards :members="$group->members" :search="$search" />

            @if ($group->paginator->hasPages())
                <div class="d-flex justify-content-center mt-3"
                    wire:key="public-member-group-pagination-{{ $group->section_key }}">
                    {{ $group->paginator->links() }}
                </div>
            @endif
        </section>
    @empty
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-fw fa-user-friends fa-2x mb-2"></i>
                @if (filled($search))
                    <p class="mb-0">No members match your search.</p>
                @else
                    <p class="mb-0">Member information is not available yet.</p>
                @endif
            </div>
        </div>
    @endforelse
</div>
