@if (isset($item['header']))

    {{-- Section label --}}
    @include('adminlte::client.partials.sidebar.menu-item-header')

@elseif (($item['type'] ?? null) === 'sidebar-menu-search')

    {{-- Search box — the package's own, the one the office panel's sidebar
         carries too: it searches the rendered menu, so it finds only the pages
         this portal actually offers. --}}
    @include('adminlte::partials.sidebar.menu-item-search-menu')

@elseif (! empty($item['submenu']))

    {{-- Treeview menu --}}
    @include('adminlte::client.partials.sidebar.menu-item-treeview-menu')

@else

    {{-- Link --}}
    @include('adminlte::client.partials.sidebar.menu-item-link')

@endif
