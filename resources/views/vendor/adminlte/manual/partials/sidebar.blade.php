@php
    $sidebarClass = ($side ?? 'admin') === 'portal'
        ? config('portal.classes_sidebar', 'sidebar-dark-primary elevation-4')
        : config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4');
@endphp

<aside class="main-sidebar {{ $sidebarClass }}">

    @include('adminlte::manual.partials.brand')

    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                {{-- The panel's own search box: AdminLTE's sidebar-menu-search
                     parses the menu it sits in, so it finds a manual chapter by
                     name and nothing else. --}}
                @include('adminlte::partials.sidebar.menu-item-search-menu', [
                    'item' => ['type' => 'sidebar-menu-search', 'text' => __('manual::common.search')],
                ])

                @foreach ($groups as $group)
                    @if (! empty($group['title']))
                        <li class="nav-header">{{ $group['title'] }}</li>
                    @endif

                    @foreach ($group['topics'] as $item)
                        <li class="nav-item">
                            <a href="{{ route($routeName, ['topic' => $item['key']]) }}"
                                class="nav-link {{ ($active['key'] ?? null) === $item['key'] ? 'active' : '' }}">
                                <i class="nav-icon {{ $item['icon'] ?? 'fas fa-fw fa-circle' }}"></i>
                                <p>{{ $item['title'] }}</p>
                            </a>
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </nav>
    </div>

</aside>
