<small>
    &copy; {{ date('Y') }}
    @if (! empty($home))
        <a href="{{ $home }}">{{ $hoa_short_name ?? config('thha.hoa_short_name') }}</a>.
    @else
        {{ $hoa_short_name ?? config('thha.hoa_short_name') }}.
    @endif
    All rights reserved.
</small>
