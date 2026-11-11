<li class="nav-item">

    <a class="nav-link {{ ! empty($item['active']) ? 'active' : '' }}" href="{{ $item['href'] }}">

        <i class="{{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>

        <p>{{ $item['text'] }}</p>

    </a>

</li>
