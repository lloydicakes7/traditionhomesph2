@props([
    'letterhead' => [],
])

@php
    $text = trim((string) ($letterhead['footer_text'] ?? ''));
    $image = $letterhead['footer_image'] ?? null;
    $width = (float) ($letterhead['footer_width'] ?? 30);
@endphp

{{-- The footer the print settings page writes: the association's own line and
     its brand image, nothing invented and nothing printed when the office has
     written none. --}}
@if ($text !== '' || $image)
    <table class="doc-footer">
        <colgroup>
            <col style="width: 72%">
            <col style="width: 28%">
        </colgroup>
        <tr>
            <td class="doc-footer__text">{!! $text !!}</td>
            <td class="doc-footer__logo">
                @if ($image)
                    <x-doc.logo :src="$image" :w="$width" :h="10" />
                @endif
            </td>
        </tr>
    </table>
@endif
