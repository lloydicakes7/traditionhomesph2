{{-- ===== Actions ===== --}}

<div class="dropdown">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="residentActions{{ $actionKey }}{{ $resident->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="residentActions{{ $actionKey }}{{ $resident->id }}">
        <a class="dropdown-item" href="#" data-toggle="modal"
            data-target="#residentViewModal" data-id="{{ $resident->id }}">
            <i class="fas fa-fw fa-eye mr-1"></i> View Details
        </a>

        @can('update', \App\Models\Resident::class)
            <a class="dropdown-item" href="#" data-toggle="modal"
                data-target="#residentFormModal" data-id="{{ $resident->id }}">
                <i class="fas fa-fw fa-edit mr-1"></i> Edit Information
            </a>
        @endcan

        @can('print.export')
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('rbi.form-b.download', $resident) }}">
                <i class="fas fa-fw fa-file-alt mr-1"></i> Print RBI Form B
            </a>
            <a class="dropdown-item"
                href="{{ route('print.pdf', ['document' => \App\Enums\PrintDocument::MEMBERSHIP_FORM, 'residentId' => $resident->id, 'download' => 1]) }}">
                <i class="fas fa-fw fa-print mr-1"></i> Print Membership Form
            </a>
        @endcan

        @canany(['update', 'delete'], \App\Models\Resident::class)
            <div class="dropdown-divider"></div>

            @can('update', \App\Models\Resident::class)
                @if ($resident->user)
                    <a class="dropdown-item text-warning" href="#"
                        wire:click.prevent="unlinkAccount({{ $resident->id }})" wire:loading.attr="disabled">
                        <i class="fas fa-fw fa-unlink mr-1"></i> Unlink Account
                    </a>
                @else
                    <a class="dropdown-item" href="#" data-toggle="modal"
                        data-target="#linkResidentUserModal" data-id="{{ $resident->id }}">
                        <i class="fas fa-fw fa-link mr-1"></i> Link Account
                    </a>
                @endif
            @endcan

            @can('delete', \App\Models\Resident::class)
                <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                    data-target="#residentDeleteModal" data-id="{{ $resident->id }}">
                    <i class="fas fa-fw fa-trash mr-1"></i> Delete
                </a>
            @endcan
        @endcanany
    </div>
</div>
