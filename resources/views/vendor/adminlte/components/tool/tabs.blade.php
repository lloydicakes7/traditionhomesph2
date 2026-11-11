<div {{ $attributes->merge(['class' => 'adminlte-tabs']) }}>
    {{-- Tab navigation --}}
    <ul class="{{ $makeNavClass() }}" role="tablist">
        @foreach($tabs as $tab)
            <li class="nav-item">
                <a class="nav-link @if(!empty($tab['active'])) active @endif"
                   data-toggle="tab"
                   href="#{{ $tab['id'] }}"
                   role="tab"
                   @if(!empty($tab['active'])) aria-selected="true" @endif>
                    @if(!empty($tab['icon']))<i class="{{ $tab['icon'] }} mr-1"></i>@endif
                    {{ $tab['title'] }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Tab content --}}
    <div class="tab-content mt-3">
        @foreach($tabs as $tab)
            <div class="tab-pane fade @if(!empty($tab['active'])) show active @endif"
                 id="{{ $tab['id'] }}"
                 role="tabpanel">
                {{ ${$tab['id']} ?? $slot }}
            </div>
        @endforeach
    </div>
</div>
