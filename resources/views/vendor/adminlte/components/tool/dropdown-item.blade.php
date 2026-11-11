@isset($divider)
    <div class="dropdown-divider"></div>
@endisset

@isset($url)
    <a class="dropdown-item" href="{{ $url }}" {{ $attributes }}>
        @isset($icon)<i class="{{ $icon }} mr-2"></i>@endisset
        {{ $text }}
    </a>
@else
    <span class="dropdown-item" {{ $attributes }}>
        @isset($icon)<i class="{{ $icon }} mr-2"></i>@endisset
        {{ $text }}
    </span>
@endisset
