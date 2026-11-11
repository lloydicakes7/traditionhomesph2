@props([
    'letterhead' => [],
    'divider'    => null,
])

@php
    $headerHtml = trim((string) ($letterhead['header_html'] ?? ''));
    $leftImage = $letterhead['left_image'] ?? null;
    $rightImage = $letterhead['right_image'] ?? null;
    $leftWidth = (float) ($letterhead['left_width'] ?? 24);
    $rightWidth = (float) ($letterhead['right_width'] ?? 24);
    $showDivider = $divider === null ? (bool) ($letterhead['divider'] ?? true) : (bool) $divider;
@endphp

{{-- The one letterhead every page heads with: the association's header text
     between its two marks, and the rule under it. Every part of it is a print
     setting, so a change made on the print settings page is on the next sheet
     and no document draws a header of its own. --}}
<table class="doc-letterhead">
    <colgroup>
        <col style="width: 19%">
        <col style="width: 62%">
        <col style="width: 19%">
    </colgroup>
    <tr>
        <td class="doc-letterhead__logo">
            @if ($leftImage)
                <x-doc.logo :src="$leftImage" :w="$leftWidth" :h="16" />
            @endif
        </td>
        <td class="doc-letterhead__text">{!! $headerHtml !!}</td>
        <td class="doc-letterhead__logo">
            @if ($rightImage)
                <x-doc.logo :src="$rightImage" :w="$rightWidth" :h="16" />
            @endif
        </td>
    </tr>
</table>

@if ($showDivider)
    <div class="doc-rule"></div>
@endif
