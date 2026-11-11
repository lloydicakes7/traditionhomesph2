@php
    $logoUrl = \App\Utility\StorageHelper::photoUrl(
        $logo_path ?? '',
        asset(config('thha.logo_path', 'image/logo.jpg'))
    );
@endphp

<a href="{{ route($homeRoute) }}" class="brand-link">
    <img src="{{ $logoUrl }}" alt="{{ $hoa_name }} logo"
        class="brand-image rounded elevation-3">

    <span class="brand-text font-weight-light">
        {{ $hoa_short_name }}
    </span>
</a>
