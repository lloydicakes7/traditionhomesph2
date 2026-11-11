@php($user = auth('homeowner')->user())

<li class="nav-item dropdown user-menu">

    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if ($user->photo_url)
            <img src="{{ $user->photo_url }}" class="user-image img-circle elevation-2"
                alt="{{ $user->name }}">
        @else
            <img src="{{ asset('image/user.png') }}"
                class="user-image img-circle elevation-2" alt="{{ $user->name }}">
        @endif

        <span class="d-none d-md-inline">{{ $user->name }}</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        <li class="user-header bg-primary h-auto">
            <p class="mt-0">
                {{ $user->name }}
                <small>{{ $user->email }}</small>
            </p>
        </li>

        <li class="user-footer d-flex flex-column">
            <a href="{{ route('portal.profile') }}" class="dropdown-item px-4 py-3 d-flex align-items-center">
                <i class="fas fa-fw fa-id-badge mr-3 fa-lg"></i>
                <span>
                    <strong class="d-block">Account Details</strong>
                    <small class="text-muted">Photo, email address and password</small>
                </span>
            </a>

            <a href="{{ route('portal.personal-information') }}" class="dropdown-item px-4 py-3 d-flex align-items-center">
                <i class="fas fa-fw fa-user-edit mr-3 fa-lg"></i>
                <span>
                    <strong class="d-block">Personal Information</strong>
                    <small class="text-muted">Your residential information on file</small>
                </span>
            </a>

            <div class="dropdown-divider m-0"></div>

            <a href="#" class="dropdown-item px-4 py-3 d-flex align-items-center text-danger"
               onclick="event.preventDefault(); document.getElementById('client-logout-form').submit();">
                <i class="fas fa-fw fa-sign-out-alt mr-3 fa-lg"></i>
                <span>
                    <strong class="d-block">{{ __('adminlte::adminlte.log_out') }}</strong>
                    <small class="text-muted">Sign out of your session</small>
                </span>
            </a>

            <form id="client-logout-form" action="{{ route('portal.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>

    </ul>

</li>
