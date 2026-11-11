@php
    $logoUrl = \App\Utility\StorageHelper::photoUrl(
        $logo_path ?? '',
        asset(config('thha.logo_path', 'image/logo.jpg'))
    );
@endphp

<div class="preloader flex-column justify-content-center align-items-center">

    <img src="{{ $logoUrl }}" class="img-circle animation__shake" alt="{{ $hoa_name }} logo"
        width="60" height="60" style="animation-iteration-count:infinite;">

</div>
