{{-- One address's actions, as the addresses table and its mobile cards both
     render them.

     The whole set of moves in one dropdown, the way the Collections and Support
     tables lay theirs out. The dropdown is also what makes the two branches
     agree: before it, the mobile card opened the dialogs through wire:click
     handlers the list does not have.

     `$actionKey` namespaces the dropdown's id: the desktop row and the mobile
     card are both on the page, and only one of them is ever visible. The same
     partial renders both.
--}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="addressActions{{ $actionKey }}{{ $address->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="addressActions{{ $actionKey }}{{ $address->id }}">
        @can('update', \App\Models\Address::class)
            <a class="dropdown-item" href="#"
                wire:click.prevent="togglePrimary({{ $address->id }})" wire:loading.attr="disabled">
                <i class="fas fa-fw fa-{{ $address->is_primary ? 'star' : 'circle' }} mr-1"></i>
                {{ $address->is_primary ? 'Remove Primary' : 'Set Primary' }}
            </a>
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#addressFormModal" data-id="{{ $address->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit
            </a>
        @endcan

        @can('delete', \App\Models\Address::class)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#addressDeleteModal" data-id="{{ $address->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
