<div {{ $attributes->merge(['class' => $makeWrapperClass()]) }}>
    <button class="{{ $makeButtonClass() }}" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        @isset($icon)<i class="{{ $icon }} mr-1"></i>@endisset
        {{ $text }}
    </button>
    <div class="dropdown-menu">
        {{ $slot }}
    </div>
</div>
