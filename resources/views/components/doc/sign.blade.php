{{--
    A signature block.

    `line` (the default) prints the name on the signature rule, which is how the
    association's framed letters sign: the name is underlined and its office is
    printed beneath. `above` prints an empty signature rule with the name under
    it, which is how a form the signer fills in by hand is laid out.
--}}
@props([
    'name'    => '',
    'role'    => '',
    'w'       => 55,
    'space'   => 18,
    'variant' => 'line',
    'strong'  => false,
    'note'    => '',
])

@php
    $text = \App\Utility\DocumentText::clean($name);
    $width = (float) $w;
    $gap = (float) $space;
@endphp

<div class="doc-sign">
    @if ($variant === 'above')
        <div style="height: {{ $gap }}mm;">&#160;</div>
        <div class="doc-sign__rule" style="width: {{ $width }}mm;">&#160;</div>
        <div class="doc-sign__name {{ $strong ? 'doc-bold' : '' }}">{{ $text }}</div>
        <div class="doc-sign__role">{{ $role }}</div>
        @if ($note !== '')
            <div class="doc-sign__role">{{ $note }}</div>
        @endif
    @else
        <div style="height: {{ $gap }}mm;">&#160;</div>
        <div class="doc-sign__line" style="width: {{ $width }}mm;">{!! $text !== '' ? e($text) : '&#160;' !!}</div>
        <div class="doc-sign__role">{{ $role }}</div>
    @endif
</div>
