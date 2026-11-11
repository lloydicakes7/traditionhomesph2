<span {{ $attributes->merge(['class' => $makeBadgeClass()]) }}>
    @isset($icon)<i class="{{ $icon }} mr-1"></i>@endisset
    {{ $text }}
</span>
