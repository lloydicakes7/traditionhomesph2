@props([
    'style' => 'plain',
    'text'  => '',
])

@php
    $classes = array_map(function ($name) {
        return 'doc-title--' . trim($name);
    }, explode(' ', $style));
@endphp

<div class="doc-title {{ implode(' ', $classes) }}">{{ $text }}</div>
