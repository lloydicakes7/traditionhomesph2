@extends('adminlte::public.public-page', [
    'name'        => $hoa_name,
    'short_name'  => $hoa_short_name,
    'description' => $hoa_description,
    'logo_path'   => $logo_path,

    'contact'     => [
        'address'        => $hoa_address,
        'website'        => $website,
        'email'          => $email,
        'contact_number' => $contact_number,
    ],

    'menuLinks'   => $menuLinks,
    'publicLinks' => $publicLinks,
    'footerLinks' => $footerLinks,
    'contentMenu' => $contentMenu ?? [],
])

@section('js')
    <script src="{{ asset('js/modal-loader.js') }}"></script>
    <script src="{{ asset('js/form-input.js') }}"></script>
    <script src="{{ asset('js/required-field.js') }}?v={{ filemtime(public_path('js/required-field.js')) }}"></script>
    <script src="{{ asset('js/contact-input.js') }}"></script>
    <script src="{{ asset('js/public-navbar.js') }}"></script>
    <script src="{{ asset('js/clock.js') }}"></script>
@stop
