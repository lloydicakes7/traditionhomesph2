@php($isPortal = ($side ?? 'admin') === 'portal')

<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    {{-- Left: sidebar toggler --}}
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
                <span class="sr-only">{{ __('adminlte::adminlte.toggle_navigation') }}</span>
            </a>
        </li>
    </ul>

    {{-- Right: language, fullscreen toggle, then the signed-in account --}}
    <ul class="navbar-nav ml-auto">

        {{-- The manual's language. Short labels only, and an auto-width
             select, so the bar still fits a small screen. Changing it reloads
             the same chapter in the chosen language. --}}
        <li class="nav-item d-flex align-items-center px-2">
            <form method="GET" action="{{ route($routeName, ['topic' => $active['key'] ?? null]) }}">
                <label class="sr-only" for="manual-locale">{{ __('manual::common.language') }}</label>
                <select id="manual-locale" name="lang"
                    class="custom-select custom-select-sm w-auto"
                    onchange="this.form.submit()">
                    @foreach ($locales as $code => $label)
                        <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </li>

        @include($isPortal
            ? 'adminlte::client.partials.navbar.menu-item-fullscreen-widget'
            : 'adminlte::partials.navbar.menu-item-fullscreen-widget')

        @if ($isPortal)
            @auth('homeowner')
                @include('adminlte::client.partials.navbar.user-menu')
            @endauth
        @else
            @auth
                @if (config('adminlte.usermenu_enabled'))
                    @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
                @else
                    @include('adminlte::partials.navbar.menu-item-logout-link')
                @endif
            @endauth
        @endif

    </ul>

</nav>
