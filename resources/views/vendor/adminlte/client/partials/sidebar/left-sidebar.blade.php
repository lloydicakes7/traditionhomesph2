<aside class="main-sidebar {{ config('portal.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand --}}
    @include('adminlte::client.partials.sidebar.brand')

    {{-- Sidebar menu — resolved from config/client.php by ClientPageComposer --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('portal.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu">
                @each('adminlte::client.partials.sidebar.menu-item', $portalMenu, 'item')
            </ul>
        </nav>
    </div>

</aside>
