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

    {{-- Right: the signed-in homeowner's menu --}}
    <ul class="navbar-nav ml-auto">

        {{-- Notifications — the portal's own feed, polled exactly like the
             office panel's bell (config/client.php keeps the link's own data
             and the update_cfg the package reads). --}}
        <x-adminlte-notification-bell
            :id="config('portal.notification.id')"
            :href="route(config('portal.notification.route'))"
            :icon="config('portal.notification.icon')"
            :badge-color="config('portal.notification.badge_color')"
            :update-cfg="config('portal.notification.update_cfg')"
            :enable-dropdown-mode="true"
            :dropdown-footer-label="config('portal.notification.dropdown_footer_label')"
            :guard="config('portal.notification.guard')" />

        {{-- Fullscreen toggle widget --}}
        @include('adminlte::client.partials.navbar.menu-item-fullscreen-widget')

        @auth('homeowner')
            @include('adminlte::client.partials.navbar.user-menu')
        @endauth
    </ul>

</nav>
