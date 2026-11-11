<span {{ $attributes->merge(['class' => 'status-indicator d-inline-flex align-items-center']) }}>
    @if(isset($dot))
        <span class="{{ $makeDotClass() }} d-inline-block rounded-circle mr-2" style="width:10px;height:10px;"></span>
    @endif
    @isset($icon)<i class="{{ $icon }} mr-2 text-{{ $theme }}"></i>@endisset
    <span class="status-text text-{{ $theme }}">{{ $text }}</span>
</span>
