@props([
    'src' => null,
    'w'   => 30,
    'h'   => 18,
])

@php
    $source = is_string($src) ? trim($src) : '';
    $embedded = strpos($source, 'data:') === 0;
    $drawable = $source !== '' && ($embedded || is_file($source));
@endphp

{{-- A logo keeps its box whether or not the association has dropped the file
     in: a missing image must never move the text beside it. --}}
@if ($drawable)
    <img class="doc-logo" src="{{ $embedded ? $source : str_replace('\\', '/', $source) }}"
        style="width: {{ $w }}mm; height: {{ $h }}mm;" alt="">
@else
    <span class="doc-logo doc-logo--empty" style="width: {{ $w }}mm; height: {{ $h }}mm;">&#160;</span>
@endif
