<li class="nav-item has-treeview {{ ! empty($item['active']) ? 'menu-open' : '' }}">

    {{-- Menu toggler --}}
    <a class="nav-link {{ ! empty($item['active']) ? 'active' : '' }}" href="">

        <i class="{{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>

        <p>
            {{ $item['text'] }}
            <i class="fas fa-angle-left right"></i>
        </p>

    </a>

    {{-- Menu items --}}
    <ul class="nav nav-treeview">
        @each('adminlte::client.partials.sidebar.menu-item', $item['submenu'], 'item')
    </ul>

</li>
