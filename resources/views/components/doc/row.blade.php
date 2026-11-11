@props([
    'label'  => '',
    'labelW' => 0,
    'value'  => '',
])

@php
    $text = \App\Utility\DocumentText::clean($value);
    $labelWidth = (float) $labelW;
@endphp

<table class="doc-row">
    <tr>
        <td class="doc-row__label">
            <span @if ($labelWidth > 0) style="width: {{ $labelWidth }}mm;" @endif>{{ $label }}</span>
        </td>
        <td class="doc-row__value">{!! $text !== '' ? e($text) : '&#160;' !!}</td>
    </tr>
</table>
