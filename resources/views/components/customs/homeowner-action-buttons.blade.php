@props(['user', 'resident', 'variant' => 'desktop'])

<div class="dropdown {{ $variant === 'mobile' ? 'float-right' : '' }}">
    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
        id="homeownerActions{{ ucfirst($variant) }}{{ $user->id }}" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="homeownerActions{{ ucfirst($variant) }}{{ $user->id }}">
        @if ($resident)
            @can('viewAny', \App\Models\User::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#homeownerViewModal" data-id="{{ $resident->id }}">
                    <i class="fas fa-fw fa-eye mr-1"></i> View Details
                </a>
            @endcan

            @if (auth()->user()->can('update', \App\Models\User::class)
                && auth()->user()->can('update', \App\Models\Resident::class))
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#homeownerFormModal" data-id="{{ $user->id }}">
                    <i class="fas fa-fw fa-user-edit mr-1"></i> Edit Profile
                </a>
            @endif
        @else
            @can('update', \App\Models\User::class)
                <a class="dropdown-item" href="#" data-toggle="modal"
                    data-target="#linkResidentUserModal" data-id="{{ $user->id }}">
                    <i class="fas fa-fw fa-link mr-1"></i> Link Resident
                </a>
            @endcan
        @endif

        <div class="dropdown-divider"></div>

        @can('resetPassword', \App\Models\User::class)
            <a class="dropdown-item text-warning" href="#" data-toggle="modal"
                data-target="#homeownerResetPasswordModal" data-id="{{ $user->id }}">
                <i class="fas fa-fw fa-key mr-1"></i> Reset Password
            </a>
        @endcan

        @can('toggleActive', $user)
            @if ($user->is_active)
                <a class="dropdown-item text-warning" href="#"
                    wire:click.prevent="toggleAccountStatus({{ $user->id }})" wire:loading.attr="disabled"
                    wire:confirm="Deactivate account for {{ $user->name }}?">
                    <i class="fas fa-fw fa-ban mr-1"></i> Deactivate Account
                </a>
            @else
                <a class="dropdown-item text-success" href="#"
                    wire:click.prevent="toggleAccountStatus({{ $user->id }})" wire:loading.attr="disabled"
                    wire:confirm="Activate account for {{ $user->name }}?">
                    <i class="fas fa-fw fa-check-circle mr-1"></i> Activate Account
                </a>
            @endif
        @endcan

        @can('delete', $user)
            <a class="dropdown-item text-danger" href="#" data-toggle="modal"
                data-target="#homeownerDeleteModal" data-id="{{ $user->id }}">
                <i class="fas fa-fw fa-trash mr-1"></i> Delete
            </a>
        @endcan
    </div>
</div>
