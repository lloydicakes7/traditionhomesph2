@php
    $paperSize = strtolower($letterhead['paper'] ?? 'a4');
    $pageOrientation = ($orientation ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
    $printSheet = public_path('css/print.css');
@endphp
@if (is_file($printSheet))
    <style>{!! file_get_contents($printSheet) !!}</style>
@endif
<style>@page {
    size: {{ $paperSize }} {{ $pageOrientation }};
    margin: {{ config('documents.margin.top') }}mm {{ config('documents.margin.right') }}mm {{ config('documents.margin.bottom') }}mm {{ config('documents.margin.left') }}mm;
}</style>
